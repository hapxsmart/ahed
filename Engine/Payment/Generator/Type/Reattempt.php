<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\Payment\GeneratorInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\Scheduler;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\ScheduleResult;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Reattempt
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type
 */
class Reattempt implements GeneratorInterface
{
    /**
     * @var Evaluation
     */
    private $evaluation;

    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var Incrementor
     */
    private $stateIncrementor;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Evaluation $evaluation
     * @param Scheduler $scheduler
     * @param Incrementor $stateIncrementor
     * @param PaymentFactory $paymentFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Evaluation $evaluation,
        Scheduler $scheduler,
        Incrementor $stateIncrementor,
        PaymentFactory $paymentFactory,
        DateTime $dateTime
    ) {
        $this->evaluation = $evaluation;
        $this->scheduler = $scheduler;
        $this->stateIncrementor = $stateIncrementor;
        $this->paymentFactory = $paymentFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source)
    {
        $reattempts = [];
        foreach ($source->getPayments() as $payment) {
            $profile = $payment->getProfile();
            $schedule = $payment->getSchedule();
            $isBundled = $payment->isBundled();

            $scheduleResult = $this->scheduler->schedule($payment);
            if ($scheduleResult->getType() == ScheduleResult::REATTEMPT_TYPE_RETRY) {
                /** @var Payment $reattempt */
                $reattempt = $this->paymentFactory->create();
                $reattempt->setProfileId($profile->getProfileId())
                    ->setProfile($profile)
                    ->setType(PaymentInterface::TYPE_REATTEMPT)
                    ->setPaymentStatus(PaymentInterface::STATUS_PENDING)
                    ->setScheduledAt($payment->getScheduledAt())
                    ->setRetryAt($scheduleResult->getDate())
                    ->setPaymentData(['token_id' => $profile->getPaymentTokenId()])
                    ->setTotalScheduled($payment->getTotalScheduled())
                    ->setBaseTotalScheduled($payment->getBaseTotalScheduled())
                    ->setRetriesCount(0)
                    ->setSchedule($schedule)
                    ->setScheduleId($schedule->getScheduleId());
                if (!$isBundled) {
                    $reattempt->setPaymentPeriod($payment->getPaymentPeriod());
                }
                $reattempts[] = $reattempt;
            } else {
                $paymentsDetails = $this->evaluation->evaluate(
                    $schedule,
                    $profile,
                    $scheduleResult->getDate()
                );
                foreach ($paymentsDetails as $details) {
                    /** @var Payment $reattempt */
                    $reattempt = $this->paymentFactory->create();
                    $reattempt->setProfileId($profile->getProfileId())
                        ->setProfile($profile)
                        ->setType(PaymentInterface::TYPE_REATTEMPT)
                        ->setPaymentStatus(PaymentInterface::STATUS_PENDING)
                        ->setScheduledAt($payment->getScheduledAt())
                        ->setRetryAt($details->getDate())
                        ->setPaymentData(['token_id' => $profile->getPaymentTokenId()])
                        ->setTotalScheduled(
                            $isBundled
                                ? $payment->getTotalScheduled()
                                : $details->getTotalAmount()
                        )
                        ->setBaseTotalScheduled(
                            $isBundled
                                ? $payment->getBaseTotalScheduled()
                                : $details->getBaseTotalAmount()
                        )
                        ->setRetriesCount(0)
                        ->setSchedule($schedule)
                        ->setScheduleId($schedule->getScheduleId());
                    if (!$isBundled) {
                        $reattempt->setPaymentPeriod($details->getPaymentPeriod());
                    }
                    $reattempts[] = $reattempt;
                }
            }
        }
        return $reattempts;
    }
}
