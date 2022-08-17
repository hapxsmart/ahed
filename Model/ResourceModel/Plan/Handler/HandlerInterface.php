<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Plan\Handler;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Interface HandlerInterface
 *
 * @package Aheadworks\Sarp2\Model\ResourceModel\Plan
 */
interface HandlerInterface
{
    /**
     * Process plan saving/deletion
     *
     * @param AbstractDb $resourceModel
     * @param PlanInterface $plan
     * @return void
     */
    public function process(AbstractDb $resourceModel, PlanInterface $plan);
}
