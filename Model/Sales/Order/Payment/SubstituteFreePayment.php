<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Payment;

/**
 * Class SubstituteFreePayment
 *
 * @package Aheadworks\Sarp2\Model\Sales\Order\Payment
 */
class SubstituteFreePayment extends \Magento\Payment\Model\Method\Free
{
    /**
     * @var string
     */
    private $title;

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getConfigData($field, $storeId = null)
    {
        if ('title' == $field) {
            return $this->getTitle();
        }

        return parent::getConfigData($field, $storeId);
    }

    /**
     * Set method title
     *
     * @param string $title
     * @return SubstituteFreePayment
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }
}
