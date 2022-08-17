<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;

/**
 * Class Scheduler
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt
 */
class Scheduler
{
    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var NextReattemptDate
     */
    private $nextReattemptDate;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var ScheduleResultFactory
     */
    private $resultFactory;

    /**
     * @var ValueResolver
     */
    private $schedulePeriodValueResolver;

    /**
     * @param NextPaymentDate $nextPaymentDate
     * @param NextReattemptDate $nextReattemptDate
     * @param DateTime $dateTime
     * @param CoreDate $coreDate
     * @param ScheduleResultFactory $resultFactory
     * @param ValueResolver $periodValueResolver
     */
    public function __construct(
        NextPaymentDate $nextPaymentDate,
        NextReattemptDate $nextReattemptDate,
        DateTime $dateTime,
        CoreDate $coreDate,
        ScheduleResultFactory $resultFactory,
        ValueResolver $periodValueResolver
    ) {
        $this->nextPaymentDate = $nextPaymentDate;
        $this->nextReattemptDate = $nextReattemptDate;
        $this->dateTime = $dateTime;
        $this->coreDate = $coreDate;
        $this->resultFactory = $resultFactory;
        $this->schedulePeriodValueResolver = $periodValueResolver;
    }

    /**
     * Perform scheduling of payment reattempt date
     *
     * @param PaymentInterface $payment
     * @return ScheduleResult
     */
    public function schedule($payment)
    {
        $schedule = $payment->getSchedule();
        $today = $this->dateTime->formatDate(true);

        $nextPaymentDate = $this->nextPaymentDate->getDateNext(
            $payment->getScheduledAt(),
            $this->schedulePeriodValueResolver->getPeriod($schedule),
            $this->schedulePeriodValueResolver->getFrequency($schedule)
        );
        $nextRetryDate = $this->nextReattemptDate->getDateNext($today);
        $lastRetryDate = $this->nextReattemptDate->getLastDate($today, $payment->getRetriesCount());

        $nextPaymentDateTm = $this->coreDate->gmtTimestamp($nextPaymentDate);
        $lastRetryDateTm = $this->coreDate->gmtTimestamp($lastRetryDate);

        return $lastRetryDateTm > $nextPaymentDateTm || $payment->isBundled()
            ? $this->resultFactory->create(
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_NEXT,
                    'date' => $nextRetryDate
                ]
            )
            : $this->resultFactory->create(
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_RETRY,
                    'date' => $nextRetryDate
                ]
            );
    }
}
