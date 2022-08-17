<?php
namespace Aheadworks\Sarp2\Model\Profile\Nearest;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\ProfileManagement;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Quote\Model\Quote;

class Calculator
{
    /**
     * @var ProfileManagement
     */
    private $profileManagement;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param PriceCurrency $priceCurrency
     * @param ProfileManagement $profileManagement
     */
    public function __construct(
        PriceCurrency $priceCurrency,
        ProfileManagement $profileManagement
    ) {
        $this->profileManagement = $profileManagement;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Calculate total for nearest profile
     *
     * @param Quote $quote
     * @param ProfileInterface $profile
     * @return float
     * @throws LocalizedException
     */
    public function calculateNearestProfileTotal(Quote $quote, ProfileInterface $profile): float
    {
        $total = 0.0;
        $quoteItems = $quote ? $quote->getItems() : [];
        $profileCurrencyCode = $profile->getProfileCurrencyCode();

        foreach ($quoteItems as $quoteItem) {
            $total += $quoteItem->getPrice() * $quoteItem->getQty();
        }

        $total = $this->priceCurrency->convertAndRound($total, null, $profileCurrencyCode);

        $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());
        $profileTotal = $nextPaymentInfo->getPaymentPeriod() == PaymentInterface::PERIOD_TRIAL
            ? $profile->getTrialSubtotal()
            : $profile->getRegularSubtotal();

        return $profileTotal + $total;
    }
}
