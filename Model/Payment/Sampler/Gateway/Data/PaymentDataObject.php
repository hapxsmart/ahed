<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;

/**
 * Class PaymentDataObject
 */
class PaymentDataObject extends DataObject implements PaymentDataObjectInterface
{
    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var SamplerInfoInterface
     */
    private $payment;

    /**
     * @var OrderAdapterInterface
     */
    private $order;

    /**
     * @param ProfileInterface $profile
     * @param SamplerInfoInterface $payment
     * @param OrderAdapterInterface|null $order
     */
    public function __construct(
        ProfileInterface $profile,
        SamplerInfoInterface $payment,
        OrderAdapterInterface $order = null
    ) {
        $this->profile = $profile;
        $this->payment = $payment;
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }
}
