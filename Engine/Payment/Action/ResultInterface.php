<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface ResultInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Action
 */
interface ResultInterface
{
    /**
     * Get order
     *
     * @return OrderInterface
     */
    public function getOrder();
}
