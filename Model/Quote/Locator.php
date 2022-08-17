<?php
namespace Aheadworks\Sarp2\Model\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;

class Locator
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Locate current quote ID
     *
     * @return int
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function locateCurrentQuoteId()
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->getId()) {
            $this->cartRepository->save($quote);
        }

        return $quote->getId();
    }
}
