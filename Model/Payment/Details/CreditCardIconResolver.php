<?php
namespace Aheadworks\Sarp2\Model\Payment\Details;

use Magento\Payment\Model\CcConfigProvider;

/**
 * Class CreditCardIconResolver
 *
 * @package Aheadworks\Sarp2\Model\Payment\Details
 */
class CreditCardIconResolver implements IconResolverInterface
{
    /**
     * @var CcConfigProvider
     */
    private $creditCardIconsProvider;

    /**
     * @param CcConfigProvider $creditCardIconsProvider
     */
    public function __construct(
        CcConfigProvider $creditCardIconsProvider
    ) {
        $this->creditCardIconsProvider = $creditCardIconsProvider;
    }

    /**
     * Retrieve icon data array for specific credit card type
     *
     * @param string $paymentType
     * @return array
     */
    public function getIconData($paymentType)
    {
        if (isset($this->creditCardIconsProvider->getIcons()[$paymentType])) {
            return $this->creditCardIconsProvider->getIcons()[$paymentType];
        }

        return [
            'url' => '',
            'width' => 0,
            'height' => 0
        ];
    }
}
