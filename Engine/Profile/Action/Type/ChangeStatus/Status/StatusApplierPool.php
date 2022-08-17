<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status;

use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type\Active;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type\Cancelled;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type\DefaultStatus;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class StatusApplierPool
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Applier
 */
class StatusApplierPool
{
    /**
     * @var StatusApplierInterface[]
     */
    private $applierInstances = [];

    /**
     * @var array
     */
    private $appliers = [
        Status::ACTIVE => Active::class,
        Status::CANCELLED => Cancelled::class,
    ];

    /**
     * @var StatusApplierFactory
     */
    private $applierFactory;

    /**
     * @param StatusApplierFactory $applierFactory
     * @param array $appliers
     */
    public function __construct(
        StatusApplierFactory $applierFactory,
        array $appliers = []
    ) {
        $this->applierFactory = $applierFactory;
        $this->appliers = array_merge($this->appliers, $appliers);
    }

    /**
     * Get action applier instance
     *
     * @param string $status
     * @return StatusApplierInterface
     */
    public function getApplier($status)
    {
        if (!isset($this->applierInstances[$status])) {
            $this->applierInstances[$status] = $this->applierFactory->create(
                $this->appliers[$status] ?? DefaultStatus::class
            );
        }
        return $this->applierInstances[$status];
    }
}
