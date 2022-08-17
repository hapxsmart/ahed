<?php
namespace Aheadworks\Sarp2\Model\Checkout\Plugin;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Session\SuccessValidator as CheckoutSuccessValidator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class SuccessValidator
 * @package Aheadworks\Sarp2\Model\Checkout\Plugin
 */
class SuccessValidator
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param HasSubscriptions $quoteChecker
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param CheckoutSuccessValidator $subject
     * @param \Closure $proceed
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsValid(CheckoutSuccessValidator $subject, \Closure $proceed)
    {
        $isValid = false;
        $quoteId = $this->checkoutSession->getLastSuccessQuoteId();
        if ($quoteId) {
            /** @var Quote $quote */
            $quote = $this->quoteRepository->get($quoteId);

            $isValid = $proceed();
            if ($isValid && $this->quoteChecker->check($quote)) {
                $isValid = $isValid && $this->checkoutSession->getLastSuccessProfileIds() !== null;
            }
        }
        return $isValid;
    }
}
