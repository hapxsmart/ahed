<?php
namespace Aheadworks\Sarp2\Block\Email\Items;

use Magento\Framework\View\Element\Template;

/**
 * Class AbstractItems
 * @package Aheadworks\Sarp2\Block\Email\Items
 */
abstract class AbstractItems extends Template
{
    /**
     * Get item row html
     *
     * @param object $item
     * @return  string
     */
    public function getItemHtml($item)
    {
        /** @var \Magento\Framework\View\Element\RendererList $rendererList */
        $rendererList = $this->getChildBlock('renderer.list');
        if (!$rendererList) {
            throw new \RuntimeException(
                'Renderer list for block "' . $this->getNameInLayout() . '" is not defined'
            );
        }
        return $rendererList->getRenderer($this->getItemType($item), 'default')
            ->setRenderedBlock($this)
            ->setItem($item)
            ->toHtml();
    }

    /**
     * Get item type
     *
     * @param object $item
     * @return string
     */
    abstract protected function getItemType($item);
}
