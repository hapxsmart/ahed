<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\OfflinePayment;

use Aheadworks\Sarp2\Gateway\AbstractTokenAssigner;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface as QuoteInfoInterface;

/**
 * Class Adapter
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\OfflinePayment
 */
class Adapter implements SamplerInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * {@inheritdoc}
     */
    public function assignData(SamplerInfoInterface $samplerPaymentInfo, DataObject $data)
    {
        return $samplerPaymentInfo
            ->setAdditionalInformation(AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID, null)
            ->setAdditionalInformation(AbstractTokenAssigner::SARP_SKIP_PAYMENT_TOKEN, true);
    }

    /**
     * {@inheritdoc}
     */
    public function place(SamplerInfoInterface $samplerPaymentInfo, QuoteInfoInterface $quotePaymentInfo)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function revert(SamplerInfoInterface $samplerPaymentInfo)
    {
        return $this;
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return true;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return true;
    }

    /**
     * Check void command availability
     *
     * @return bool
     */
    public function canVoid()
    {
        return true;
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStore()
    {
        return $this->storeId;
    }
}
