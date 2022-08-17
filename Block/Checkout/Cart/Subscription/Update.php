<?php
namespace Aheadworks\Sarp2\Block\Checkout\Cart\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Nearest\Calculator;
use Aheadworks\Sarp2\Model\Profile\Nearest\Provider;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote;

class Update extends Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Provider
     */
    private $nearestProfileProvider;

    /**
     * @var Calculator
     */
    private $nearestProfileCalculator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Calculator $nearestProfileCalculator
     * @param CustomerSession $customerSession
     * @param Template\Context $context
     * @param Provider $nearestProfileProvider
     * @param PriceCurrency $priceCurrency
     * @param Session $checkoutSession
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Calculator $nearestProfileCalculator,
        CustomerSession $customerSession,
        Template\Context $context,
        Provider $nearestProfileProvider,
        PriceCurrency $priceCurrency,
        Session $checkoutSession,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->nearestProfileProvider = $nearestProfileProvider;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
        $this->nearestProfileCalculator = $nearestProfileCalculator;
        $this->config = $config;
    }

    /**
     * Get update subscription URL
     *
     * @return string
     */
    public function getUpdateUrl(): string
    {
        return $this->templateContext->getUrl('aw_sarp2/profile/edit_additem');
    }

    /**
     * Check is add item allowed
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        if (!$this->config->canAddToNearestSubscription()) {
            return false;
        }

        try {
            if ($this->getNearestProfile() && $this->getQuote()) {
                return true;
            }
        } catch (LocalizedException $e) {
            return false;
        }

        return false;
    }

    /**
     * Calculate updated subscription totals
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSubscriptionTotal(): string
    {
        $nearestProfile = $this->getNearestProfile();
        $quote = $this->getQuote();
        $total = $this->nearestProfileCalculator->calculateNearestProfileTotal($quote, $nearestProfile);

        return $this->priceCurrency->format(
            $total,
            true,
            2,
            null,
            $nearestProfile->getProfileCurrencyCode()
        );
    }

    /**
     * Get nearest customer profile
     *
     * @return ProfileInterface
     * @throws LocalizedException
     */
    private function getNearestProfile(): ?ProfileInterface
    {
        $customerId = $this->customerSession->getCustomerId();
        $storeId = $this->_storeManager->getStore()->getId();

        return $this->nearestProfileProvider->getNearestProfile($customerId, $storeId);
    }

    /**
     * Check if current customer quote has subscription items
     *
     * @return Quote|null
     */
    private function getQuote(): ?Quote
    {
        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (\Exception $e) {
            return null;
        }

        return $quote;
    }
}
