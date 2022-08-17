<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\Validator;

use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Aheadworks\Sarp2\Model\Profile\ItemManagement;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class IsItemAvailable
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\Validator
 */
class IsItemAvailable extends AbstractValidator
{
    /**
     * @var ItemManagement
     */
    private $itemManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ItemManagement $itemManagement
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ItemManagement $itemManagement,
        ProductRepositoryInterface $productRepository
    ) {
        $this->itemManagement = $itemManagement;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    protected function performValidation($profile, $action)
    {
        $itemId = $action->getData()->getItemId();
        $item = $this->itemManagement->getItemFromProfileById($itemId, $profile);

        if ($item) {
            if ($item->getParentItemId()) {
                $this->addMessages(['Editing of this item is not available.']);
            }
            if ($item->getReplacementItemId()) {
                $this->addMessages(['Editing of this item is not available.']);
            }
            if (!$this->isSalableProduct($item->getProductId())) {
                $this->addMessages(['Product is not available.']);
            }
        } else {
            $this->addMessages(['Item does not belong to subscription.']);
        }
    }

    /**
     * Is salable product
     *
     * @param int $productId
     * @return bool
     */
    private function isSalableProduct($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $isSalable = $product->isSalable();
        } catch (NoSuchEntityException $exception) {
            $isSalable = false;
        }

        return $isSalable;
    }
}
