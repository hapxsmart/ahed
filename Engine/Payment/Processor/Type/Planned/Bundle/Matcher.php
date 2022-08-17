<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Checker\MergeableInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDateTime;

/**
 * Class Matcher
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle
 */
class Matcher
{
    /**
     * @var MergeableInterface
     */
    private $mergeAbleChecker;

    /**
     * @var CoreDateTime
     */
    private $coreDate;

    /**
     * @var array
     */
    private $matchResults = [];

    /**
     * @param MergeableInterface $mergeAbleChecker
     * @param CoreDateTime $coreDate
     */
    public function __construct(
        MergeableInterface $mergeAbleChecker,
        CoreDateTime $coreDate
    ) {
        $this->mergeAbleChecker = $mergeAbleChecker;
        $this->coreDate = $coreDate;
    }

    /**
     * Check if payments are match to be joined into bundled payment
     *
     * @param PaymentInterface $payment1
     * @param PaymentInterface $payment2
     * @return bool
     */
    public function match($payment1, $payment2)
    {
        $payment1Id = $payment1->getId();
        $payment2Id = $payment2->getId();

        $key = $payment1Id . '-' . $payment2Id;
        $keyReversed = $payment2Id . '-' . $payment1Id;

        if (!isset($this->matchResults[$key]) || !isset($this->matchResults[$keyReversed])) {
            $scheduledAt1 = $this->coreDate->gmtDate(
                DateTime::DATE_PHP_FORMAT,
                $payment1->getScheduledAt()
            );
            $scheduledAt2 = $this->coreDate->gmtDate(
                DateTime::DATE_PHP_FORMAT,
                $payment2->getScheduledAt()
            );
            $isMatch = $scheduledAt1 == $scheduledAt2
                && $this->mergeAbleChecker->check($payment1->getProfile(), $payment2->getProfile());

            $this->matchResults[$key] = $isMatch;
            $this->matchResults[$keyReversed] = $isMatch;

            return $isMatch;
        } elseif (isset($this->matchResults[$key])) {
            return $this->matchResults[$key];
        } else {
            return $this->matchResults[$keyReversed];
        }
    }
}
