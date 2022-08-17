<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Model\Profile\Source\Status;

class StatusMap
{
    /**
     * @var array
     */
    private $map = [
        Status::ACTIVE => [Status::ACTIVE, Status::SUSPENDED, Status::CANCELLED, Status::EXPIRED],
        Status::SUSPENDED => [Status::ACTIVE, Status::CANCELLED, Status::PENDING],
        Status::PENDING => [Status::ACTIVE, Status::CANCELLED, Status::SUSPENDED],
        Status::EXPIRED => [],
        Status::CANCELLED => [Status::ACTIVE]
    ];

    /**
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = array_merge($this->map, $map);
    }

    /**
     * Get all profile statuses
     *
     * @return array
     */
    public function getAllStatuses()
    {
        return array_keys($this->map);
    }

    /**
     * Get allowed profile statuses
     *
     * @param string $status
     * @return array
     */
    public function getAllowedStatuses($status)
    {
        return isset($this->map[$status])
            ? $this->map[$status]
            : [];
    }
}
