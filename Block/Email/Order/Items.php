<?php
declare(strict_types=1);

namespace Aheadworks\Sarp2\Block\Email\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Aheadworks\Sarp2\Block\Email\Items\AbstractItems;

/**
 * Class Items
 *
 * @method \Aheadworks\Sarp2\ViewModel\Order getOrderViewModel()
 */
class Items extends AbstractItems
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'email/order/items.phtml';

    /**
     * @inheritdoc
     */
    protected function getItemType($item) : string
    {
        /** @var OrderItemInterface $item */
        return $item->getProductType();
    }

    /**
     * Get order
     *
     * @return OrderInterface
     */
    public function getOrder() : OrderInterface
    {
        $order = $this->getData('order');
        return $order ?? $this->getOrderViewModel()->getOrder((int)$this->getData('order_id'));
    }
}
