<?php
namespace Aheadworks\Sarp2\Test\Integration\Model\ResourceModel;

use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ProfileStub
 * @package Aheadworks\Sarp2\Test\Integration\Model\ResourceModel
 */
class ProfileStub extends ProfileResource
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(AbstractModel $object)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(AbstractModel $object)
    {
        return $this;
    }
}
