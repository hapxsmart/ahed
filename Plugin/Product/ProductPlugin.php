<?php
namespace Aheadworks\Sarp2\Plugin\Product;

use Aheadworks\Sarp2\Model\Product\Type\Bundle\PriceModelSubstitute;
use Aheadworks\Sarp2\Model\Product\Type\Bundle\PriceModelSubstituteFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;

/**
 * Class ProductPlugin
 */
class ProductPlugin
{
    /**
     * @var PriceModelSubstituteFactory
     */
    private $bundlePriceModelSubstituteFactory;

    /**
     * @var PriceModelSubstitute
     */
    private $priceModelWrapper;

    /**
     * @param PriceModelSubstituteFactory $priceModelFactory
     */
    public function __construct(PriceModelSubstituteFactory $priceModelFactory)
    {
        $this->bundlePriceModelSubstituteFactory = $priceModelFactory;
    }

    /**
     * @param Product $subject
     * @param callable $proceed
     * @return Price
     */
    public function aroundGetPriceModel(Product $subject, callable $proceed)
    {
        if ($subject->getTypeId() == Type::TYPE_CODE
            && $subject->getData(PriceModelSubstitute::DO_NOT_USE_ADVANCED_PRICES_FOR_BUNDLE)
        ) {
            return $this->getPriceModelSubstitution();
        } else {
            return $proceed();
        }
    }

    /**
     * @return PriceModelSubstitute
     */
    private function getPriceModelSubstitution()
    {
        if (!$this->priceModelWrapper) {
            $this->priceModelWrapper = $this->bundlePriceModelSubstituteFactory->create();
        }

        return $this->priceModelWrapper;
    }
}
