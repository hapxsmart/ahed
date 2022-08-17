<?php
namespace Aheadworks\Sarp2\Ui\DataProvider\Filters;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

/**
 * Class Customer
 * @package Aheadworks\Sarp2\Ui\DataProvider\Filters
 */
class Customer implements FilterApplierInterface
{
    /**
     * Apply customer filters like collection filters
     *
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(Collection $collection, Filter $filter)
    {
        $collection->addFieldToFilter($filter->getField(), [$filter->getConditionType() => $filter->getValue()]);
    }
}
