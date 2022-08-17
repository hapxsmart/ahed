<?php
namespace Aheadworks\Sarp2\Model\Payment\Checks\Plugin;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Payment\Model\Checks\ZeroTotal as Checker;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Class ZeroTotal
 * @package Aheadworks\Sarp2\Model\Payment\Checks\Plugin
 */
class ZeroTotal
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @param HasSubscriptions $quoteChecker
     */
    public function __construct(HasSubscriptions $quoteChecker)
    {
        $this->quoteChecker = $quoteChecker;
    }

    /**
     * @param Checker $subject
     * @param bool $result
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsApplicable(
        Checker $subject,
        $result,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        return $result || $this->quoteChecker->checkHasSubscriptionsOnly($quote);
    }
}
