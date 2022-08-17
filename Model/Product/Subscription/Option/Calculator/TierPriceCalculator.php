<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option\Calculator;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory as CalculationInputFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Locale\Format;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

class TierPriceCalculator
{
    /**
     * @var CalculationInputFactory
     */
    private $calculationInputFactory;

    /**
     * @var SubscriptionPriceCalculatorInterface
     */
    private $subscriptionPriceCalculator;

    /**
     * @var Format
     */
    private $localeFormat;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param CalculationInputFactory $calculationInputFactory
     * @param SubscriptionPriceCalculatorInterface $calculation
     * @param Format $localeFormat
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        CalculationInputFactory $calculationInputFactory,
        SubscriptionPriceCalculatorInterface $calculation,
        Format $localeFormat,
        PriceCurrency $priceCurrency
    ) {
        $this->calculationInputFactory = $calculationInputFactory;
        $this->subscriptionPriceCalculator = $calculation;
        $this->localeFormat = $localeFormat;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get regular product tier prices
     *
     * @param ProductInterface|Product $product
     * @return array
     */
    public function getRegularTierPrices($product)
    {
        $tierPrices = [];
        $priceInfo = $product->getPriceInfo();
        $tierPriceModel = $priceInfo->getPrice(TierPrice::PRICE_CODE);
        $tierPricesList = $tierPriceModel->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $percentage = null === $tierPrice['percentage_value']
                ? $tierPriceModel->getSavePercent($tierPrice['price'])
                : $tierPrice['percentage_value'];
            $tierPrices[] = [
                'qty' => $tierPrice['price_qty'],
                'price' => $tierPrice['website_price'],
                'percentage' => $this->localeFormat->getNumber($percentage)
            ];
        }

        return $tierPrices;
    }

    /**
     * Calculate subscription tier prices
     *
     * @param ProductInterface|Product $product
     * @param SubscriptionOptionInterface $option
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateSubscriptionTierPrices($product, $option)
    {
        $tierPrices = [];
        $priceInfo = $product->getPriceInfo();
        $tierPriceModel = $priceInfo->getPrice(TierPrice::PRICE_CODE);
        $tierPricesList = $tierPriceModel->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $qty = $tierPrice['price_qty'];
            $price = $this->subscriptionPriceCalculator->getRegularPrice(
                $this->calculationInputFactory->create($product, $qty),
                $option
            );

            // recalculate percent
            $basePrice = $this->subscriptionPriceCalculator->getRegularPrice(
                $this->calculationInputFactory->create($product, 1),
                $option
            );
            if ($basePrice > 0) {
                $percent = round(
                    100 - ((100 / $basePrice) * $price)
                );
            } else {
                $percent = 0;
            }

            if ($percent > 0) {
                $tierPrices[] = [
                    'qty' => $qty,
                    'price' => $this->priceCurrency->convert($price),
                    'percentage' => $this->localeFormat->getNumber($percent)
                ];
            }
        }

        return $tierPrices;
    }
}
