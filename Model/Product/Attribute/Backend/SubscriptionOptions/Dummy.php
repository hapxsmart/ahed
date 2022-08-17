<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Class Dummy
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions
 */
class Dummy extends AbstractBackend
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($object)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isScalar()
    {
        return false;
    }
}
