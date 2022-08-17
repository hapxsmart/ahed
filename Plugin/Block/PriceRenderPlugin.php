<?php
namespace Aheadworks\Sarp2\Plugin\Block;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Render;
use Aheadworks\Sarp2\Pricing\Price\Code\Resolver as PriceCodeResolver;

/**
 * Class PriceRenderPlugin
 * @package Aheadworks\Sarp2\Plugin\Block
 */
class PriceRenderPlugin
{
    /**
     * @var PriceCodeResolver
     */
    private $priceCodeResolver;

    /**
     * @param PriceCodeResolver $priceCodeResolver
     */
    public function __construct(
        PriceCodeResolver $priceCodeResolver
    ) {
        $this->priceCodeResolver = $priceCodeResolver;
    }

    /**
     * Set custom price renderer for only subscription products
     *
     * @param Render $subject
     * @param string $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return array
     */
    public function beforeRender($subject, $priceCode, SaleableInterface $saleableItem, array $arguments = [])
    {
        $actualPriceCode = $priceCode;
        if (isset($arguments['zone'])
            && $arguments['zone'] == Render::ZONE_ITEM_LIST
        ) {
            $actualPriceCode = $this->priceCodeResolver->getActualPriceCodeForItemList(
                $priceCode,
                $saleableItem
            );
        }

        return [$actualPriceCode, $saleableItem, $arguments];
    }
}
