<?php
namespace Aheadworks\Sarp2\Test\Integration\Model\Sales\Total\Populator;

use Magento\Directory\Model\PriceCurrency;

/**
 * Class PriceCurrencyStub
 * @package Aheadworks\Sarp2\Test\Integration\Model\Sales\Total\Populator
 */
class PriceCurrencyStub extends PriceCurrency
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function convert($amount, $scope = null, $currency = null)
    {
        return $amount * 1.5;
    }
}
