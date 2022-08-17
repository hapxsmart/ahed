<?php
namespace Aheadworks\Sarp2\Test\Integration\Model\Shipping;

use Aheadworks\Sarp2\Model\Profile\Total\Provider\RegularPrice as ProfileRegularPrice;
use Aheadworks\Sarp2\Model\Profile\Total\Provider\TrialPrice as ProfileTrialPrice;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider\Regular as QuoteRegularPrice;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider\Trial as QuoteTrialPrice;
use Aheadworks\Sarp2\Model\Shipping\RatesCollector;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class RatesCollectorStub
 * @package Aheadworks\Sarp2\Test\Integration\Model\Shipping
 */
class RatesCollectorStub extends RatesCollector
{
    /**
     * Trial shipping amount
     */
    const TRIAL_SHIPPING_AMOUNT = 3;

    /**
     * Regular shipping amount
     */
    const REGULAR_SHIPPING_AMOUNT = 5;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect($address, $additionalAddressData, $totalsProvider, $storeId = null)
    {
        $rates = [];

        $objectManager = Bootstrap::getObjectManager();
        if ($totalsProvider instanceof QuoteRegularPrice
            || $totalsProvider instanceof ProfileRegularPrice
        ) {
            $rates[] = $objectManager->create(
                Rate::class,
                [
                    'data' => [
                        'code' => 'flatrate_flatrate',
                        'price' => self::REGULAR_SHIPPING_AMOUNT
                    ]
                ]
            );
        } elseif ($totalsProvider instanceof QuoteTrialPrice
            || $totalsProvider instanceof ProfileTrialPrice
        ) {
            $rates[] = $objectManager->create(
                Rate::class,
                [
                    'data' => [
                        'code' => 'flatrate_flatrate',
                        'price' => self::TRIAL_SHIPPING_AMOUNT
                    ]
                ]
            );
        }

        return $rates;
    }
}
