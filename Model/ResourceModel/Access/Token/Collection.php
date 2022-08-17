<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Access\Token;

use Aheadworks\Sarp2\Api\Data\AccessTokenInterface;
use Aheadworks\Sarp2\Model\Access\Token;
use Aheadworks\Sarp2\Model\ResourceModel\Access\Token as TokenResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Aheadworks\Sarp2\Model\ResourceModel\Access\Token
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = AccessTokenInterface::ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Token::class, TokenResource::class);
    }
}
