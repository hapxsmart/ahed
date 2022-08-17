<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Adapter;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Payment\Method\Data\DataAssignerInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Profile\ToOrder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\PaymentInterface as QuotePaymentInfoInterface;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;

/**
 * Class ToOrderPaymentInfoConverter
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Adapter
 */
class ToOrderPaymentInfoConverter
{
    const CHECKOUT_CREATED_ORDER = 'checkout_created_order';

    /**
     * @var ToOrderPaymentConverter
     */
    private $quotePaymentToOrderPayment;

    /**
     * @var ToOrder
     */
    private $profileToOrder;

    /**
     * @var OrderPaymentInterfaceFactory
     */
    private $orderPaymentInfoFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param ToOrder $profileToOrder
     * @param OrderPaymentInterfaceFactory $orderPaymentInfoFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        ToOrder $profileToOrder,
        OrderPaymentInterfaceFactory $orderPaymentInfoFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->profileToOrder = $profileToOrder;
        $this->orderPaymentInfoFactory = $orderPaymentInfoFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Convert quote payment info model to order payment info model and create fake order
     *
     * @param QuotePaymentInfoInterface $quotePaymentInfo
     * @param ProfileInterface $profile
     * @return OrderPaymentInterface|InfoInterface
     * @throws \Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException
     */
    public function convertFromQuotePaymentInfo(QuotePaymentInfoInterface $quotePaymentInfo, ProfileInterface $profile)
    {
        $orderPaymentInfo = $this->quotePaymentToOrderPayment->convert($quotePaymentInfo);
        $order = $this->resolveOrderFromQuotePaymentInfo($quotePaymentInfo, $profile);
        $orderPaymentInfo
            ->setOrder($order)
            ->setAdditionalInformation(DataAssignerInterface::IS_SARP_TOKEN_ENABLED, true);

        return $orderPaymentInfo;
    }

    /**
     * Retrieve order created on checkout page or create fake temporary order
     *
     * @param QuotePaymentInfoInterface $quotePaymentInfo
     * @param ProfileInterface $profile
     * @return \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order
     * @throws \Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException
     */
    private function resolveOrderFromQuotePaymentInfo(
        QuotePaymentInfoInterface $quotePaymentInfo,
        ProfileInterface $profile
    ) {
        return $quotePaymentInfo->hasData(self::CHECKOUT_CREATED_ORDER)
            ? $quotePaymentInfo->getData(self::CHECKOUT_CREATED_ORDER)
            : $this->createFakeTemporaryOrderObject($profile);
    }

    /**
     * Create temporary order object
     *
     * @param ProfileInterface $profile
     * @return \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order
     * @throws \Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException
     */
    private function createFakeTemporaryOrderObject(ProfileInterface $profile)
    {
        $order = $this->profileToOrder->convert(
            $profile,
            PaymentInterface::PERIOD_INITIAL,
            false
        );
        $order->setIncrementId('tokenize_' . $profile->getIncrementId());

        return $order;
    }

    /**
     * Convert sampler payment info model to order payment info model and create fake order
     *
     * @param QuotePaymentInfoInterface $quotePaymentInfo
     * @param ProfileInterface $profile
     * @return OrderPaymentInterface|InfoInterface
     * @throws \Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException
     */
    public function convertFromSamplerPaymentInfo(SamplerInfoInterface $samplerInfo, ProfileInterface $profile)
    {
        $orderPaymentInfo = $this->orderPaymentInfoFactory->create();
        $order = $this->createFakeTemporaryOrderObject($profile);
        $this->dataObjectHelper->populateWithArray(
            $orderPaymentInfo,
            $samplerInfo->getData(),
            OrderPaymentInterface::class
        );
        $orderPaymentInfo
            ->setCcTransId($samplerInfo->getLastTransactionId())
            ->setLastTransId($samplerInfo->getLastTransactionId())
            ->setOrder($order);

        return $orderPaymentInfo;
    }
}
