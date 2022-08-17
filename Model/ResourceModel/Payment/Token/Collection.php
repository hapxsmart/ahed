<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Payment\Token;

use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Payment\Token
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'token_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Token::class, TokenResource::class);
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad()
    {
        foreach ($this as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }
}
