<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\ResolverInterface;

/**
 * Class ProductOptions
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Item
 */
class ProductOptions implements ResolverInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getResolvedValue($entities, $field)
    {
        /** @var ProfileItemInterface[] $entities */
        return $entities[0]->getProductOptions();
    }
}
