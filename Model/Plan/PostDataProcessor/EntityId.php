<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

/**
 * Class EntityId
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class EntityId implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (empty($data['plan_id'])) {
            $data['plan_id'] = null;
        }
        return $data;
    }
}
