<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions\DynamicRows;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select as HtmlSelect;

/**
 * Class Select
 * @package Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions\DynamicRows
 */
class Select extends HtmlSelect
{
    /**
     * @var OptionSourceInterface
     */
    private $optionSource;

    /**
     * @param Context $context
     * @param OptionSourceInterface $optionSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        OptionSourceInterface $optionSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->optionSource = $optionSource;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->optionSource->toOptionArray());
        }

        $html = parent::_toHtml();
        $html = str_replace('&quot;', "\'", $html);

        return $html;
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
