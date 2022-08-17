<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Engine\Entity;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Model\AbstractModel;

/**
 * Class DataPreparer
 * @package Aheadworks\Sarp2\Model\ResourceModel\Engine\Entity
 */
class DataPreparer
{
    /**
     * @var array
     */
    private $map = [
        ProfileInterface::class => [
            'fields' => [
                ProfileInterface::STATUS,
                ProfileInterface::LAST_ORDER_DATE,
                ProfileInterface::LAST_ORDER_ID
            ]
        ]
    ];

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param Factory $dataObjectFactory
     */
    public function __construct(Factory $dataObjectFactory)
    {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Prepare entity data for save
     *
     * @param AbstractModel $entity
     * @param string $entityType
     * @return DataObject
     */
    public function prepareForSave($entity, $entityType)
    {
        $fields = isset($this->map[$entityType])
            ? $this->map[$entityType]['fields']
            : [];
        $closure = function ($field) use ($fields) {
            return in_array($field, $fields);
        };
        $data = array_filter($entity->getData(), $closure, ARRAY_FILTER_USE_KEY);
        return $this->dataObjectFactory->create($data);
    }

    /**
     * Prepare entity data for update
     *
     * @param AbstractModel $entity
     * @param string $entityType
     * @return DataObject
     */
    public function prepareForUpdate($entity, $entityType)
    {
        return $this->prepareForSave($entity, $entityType);
    }
}
