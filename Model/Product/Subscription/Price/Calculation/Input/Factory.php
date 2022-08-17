<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;

use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input as CalculationInput;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param ProductInterface $product
     * @param float $qty
     * @param ProductInterface|null $childProduct
     * @param float|null $childQty
     * @return CalculationInput
     */
    public function create($product, $qty, $childProduct = null, $childQty = null)
    {
        return $this->objectManager->create(
            CalculationInput::class,
            [
                'product' => $product,
                'qty' => $qty,
                'childProduct' => $childProduct,
                'childQty' => $childQty
            ]
        );
    }
}
