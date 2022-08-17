<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Product;

use Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions\DynamicRows;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;

/**
 * Class SubscriptionOptions
 * @package Aheadworks\Sarp2\Block\Adminhtml\Product
 */
class SubscriptionOptions extends \Magento\Framework\Data\Form\Element\Text
{
    /**
     * @var DynamicRows
     */
    private $dynamicRows;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param DynamicRows $dynamicRows
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        DynamicRows $dynamicRows,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->dynamicRows = $dynamicRows;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementHtml()
    {
        $this->dynamicRows->setElement($this);
        $html = $this->dynamicRows->toHtml();

        return $html;
    }
}
