<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetails;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetailsFactory;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Checker;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\SalesRule\Rule\Calculator as RuleCalculator;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Sales\Api\Data\OrderInterface;

class Evaluation
{
    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var PaymentDetailsFactory
     */
    private $detailsFactory;

    /**
     * @var Checker
     */
    private $scheduleChecker;

    /**
     * @var ValueResolver
     */
    private $schedulePeriodValueResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RuleCalculator
     */
    private $ruleCalculator;

    /**
     * @param CoreDate $coreDate
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentDetailsFactory $detailsFactory
     * @param Checker $scheduleChecker
     * @param ValueResolver $periodValueResolver
     * @param Config $config
     * @param RuleCalculator $ruleCalculator
     */
    public function __construct(
        CoreDate $coreDate,
        NextPaymentDate $nextPaymentDate,
        PaymentDetailsFactory $detailsFactory,
        Checker $scheduleChecker,
        ValueResolver $periodValueResolver,
        Config $config,
        RuleCalculator $ruleCalculator
    ) {
        $this->coreDate = $coreDate;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->detailsFactory = $detailsFactory;
        $this->scheduleChecker = $scheduleChecker;
        $this->schedulePeriodValueResolver = $periodValueResolver;
        $this->config = $config;
        $this->ruleCalculator = $ruleCalculator;
    }

    /**
     * Evaluate possible payment details for current date.
     * Assumed that current date is a payment date candidate.
     * Returns an empty array if there is no possible payments
     *
     * @param ScheduleInterface $schedule
     * @param ProfileInterface $profile
     * @param string $currentDate
     * @param string|null $lastPaymentDate
     * @return PaymentDetails[]
     */
    public function evaluate(
        ScheduleInterface $schedule,
        ProfileInterface $profile,
        $currentDate,
        $lastPaymentDate = null
    ) {
        $details = null;

        $wasPayments = $lastPaymentDate !== null;
        $baseDate = $wasPayments
            ? $lastPaymentDate
            : $profile->getStartDate();

        $baseTm = $this->getGmtTimestampExclTime($baseDate);
        $currentTm = $this->getGmtTimestampExclTime($currentDate);

        $estimateTypes = $wasPayments
            ? $currentTm >= $this->getGmtTimestampExclTime(
                $this->nextPaymentDate->getDateNext(
                    $lastPaymentDate,
                    $this->schedulePeriodValueResolver->getPeriod($schedule),
                    $this->schedulePeriodValueResolver->getFrequency($schedule)
                )
            )
            : true;

        if ($estimateTypes) {
            if ($profile->getProfileDefinition()->getIsInitialFeeEnabled() && !$schedule->isInitialPaid()) {
                $details = $this->detailsFactory->create(
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_INITIAL,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => $profile->getStartDate(),
                        'totalAmount' => $this->getTotalAmount($profile, $profile->getInitialGrandTotal()),
                        'baseTotalAmount' => $this->getBaseTotalAmount($profile, $profile->getBaseInitialGrandTotal())
                    ]
                );
            } elseif ($baseTm <= $currentTm || !$wasPayments) {
                if ($this->scheduleChecker->isTrialNextPayment($schedule)) {
                    $details = $this->detailsFactory->create(
                        [
                            'paymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                            'paymentType' => PaymentInterface::TYPE_PLANNED,
                            'date' => $currentDate,
                            'totalAmount' => $this->getTotalAmount($profile, $profile->getTrialGrandTotal()),
                            'baseTotalAmount' => $this->getBaseTotalAmount($profile, $profile->getBaseTrialGrandTotal())
                        ]
                    );
                } else {
                    $totalRegularCounts = $schedule->getRegularTotalCount();

                    if ($schedule->isMembershipModel()
                        && $this->scheduleChecker->isFiniteSubscription($schedule)
                        && $this->scheduleChecker->isMembershipNextPayment($schedule)
                    ) {
                        $details = $this->detailsFactory->create(
                            [
                                'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                                'paymentType' => PaymentInterface::TYPE_LAST_PERIOD_HOLDER,
                                'date' => $currentDate,
                                'totalAmount' => 0,
                                'baseTotalAmount' => 0
                            ]
                        );
                    } elseif (!$this->scheduleChecker->isFiniteSubscription($schedule)
                             || $schedule->getRegularCount() < $totalRegularCounts
                    ) {
                        $details = $this->detailsFactory->create(
                            [
                                'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                                'paymentType' => PaymentInterface::TYPE_PLANNED,
                                'date' => $currentDate,
                                'totalAmount' => $this->getTotalAmount($profile, $profile->getRegularGrandTotal()),
                                'baseTotalAmount' => $this->getBaseTotalAmount(
                                    $profile,
                                    $profile->getBaseRegularGrandTotal()
                                )
                            ]
                        );
                    }
                }
            }
        }

        return $details ? [$details] : [];
    }

    /**
     * Get GMT timestamp without time
     *
     * @param string $date
     * @return int
     */
    private function getGmtTimestampExclTime($date)
    {
        $dateTime = (new \DateTime($date))
            ->setTime(0, 0, 0);
        return $this->coreDate->gmtTimestamp($dateTime);
    }

    /**
     * Get total amount
     *
     * @param ProfileInterface $profile
     * @param float $total
     * @return float
     */
    private function getTotalAmount($profile, $total)
    {
        /** @var OrderInterface $order */
        $order = $profile->getOrder();
        if ($this->config->isRecalculationOfTotalsEnabled($profile->getStoreId()) && $order) {
            $recalculatedTotal = 0;
            $orderItems = $order->getItems();
            foreach ($orderItems as $orderItem) {
                foreach ($profile->getItems() as $item) {
                    if ($orderItem->getProductId() == $item->getProductId()) {
                        $recalculatedTotal += $orderItem->getRowTotal()
                            + $orderItem->getTaxAmount()
                            - $orderItem->getDiscountAmount();
                    }
                }
            }
            $recalculatedTotal += $order->getShippingInclTax();
            $total = min($total, $recalculatedTotal);
        }

        return $total;
    }

    /**
     * Get base total amount
     *
     * @param ProfileInterface $profile
     * @param float $total
     * @return float
     */
    private function getBaseTotalAmount($profile, $total)
    {
        /** @var OrderInterface $order */
        $order = $profile->getOrder();
        if ($this->config->isRecalculationOfTotalsEnabled($profile->getStoreId()) && $order) {
            $recalculatedTotal = 0;
            $orderItems = $order->getItems();
            foreach ($orderItems as $orderItem) {
                foreach ($profile->getItems() as $item) {
                    if ($orderItem->getProductId() == $item->getProductId()) {
                        $recalculatedTotal += $orderItem->getBaseRowTotal()
                            + $orderItem->getBaseTaxAmount()
                            - $orderItem->getBaseDiscountAmount();
                    }
                }
            }
            $recalculatedTotal += $order->getBaseShippingAmount();
            $total = min($total, $recalculatedTotal);
        }

        return $total;
    }
}
