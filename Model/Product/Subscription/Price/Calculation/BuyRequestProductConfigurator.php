<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class ProductConfigurator
 */
class BuyRequestProductConfigurator
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * ProductConfigurator constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(DataObjectFactory $dataObjectFactory)
    {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Configure product by buyRequest
     *
     * @param ProductInterface|Product $product
     * @param array|DataObject $buyRequest
     */
    public function configure($product, $buyRequest)
    {
        if (!$buyRequest instanceof DataObject) {
            $buyRequest = $this->dataObjectFactory->create($buyRequest);
        }

        $product->getTypeInstance()->processConfiguration(
            $buyRequest,
            $product,
            $product->getTypeInstance()::PROCESS_MODE_FULL
        );
    }
}
