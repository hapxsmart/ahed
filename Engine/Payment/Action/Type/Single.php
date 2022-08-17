<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;
use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoFactory;
use Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException;
use Aheadworks\Sarp2\Model\Profile\ToOrder;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;

/**
 * Class Single
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Type
 */
class Single implements ActionInterface
{
    /**
     * @var ToOrder
     */
    private $converter;

    /**
     * @var PlaceOrder
     */
    private $placeOrderService;

    /**
     * @var PaymentInfoFactory
     */
    private $paymentInfoFactory;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param ToOrder $converter
     * @param PlaceOrder $placeOrderService
     * @param PaymentInfoFactory $paymentInfoFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ToOrder $converter,
        PlaceOrder $placeOrderService,
        PaymentInfoFactory $paymentInfoFactory,
        ResultFactory $resultFactory,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->converter = $converter;
        $this->placeOrderService = $placeOrderService;
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->resultFactory = $resultFactory;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function pay(PaymentInterface $payment)
    {
        $profile = $payment->getProfile();
        $paymentPeriod = $payment->getPaymentPeriod();
        $order = $this->convertProfileToOrder($profile, $paymentPeriod);
        $this->placeOrderService->place(
            $order,
            [
                $this->paymentInfoFactory->create(
                    [
                        'profile' => $profile,
                        'paymentPeriod' => $paymentPeriod
                    ]
                )
            ]
        );
        $profile
            ->setOrder($order)
            ->setLastOrderId($order->getEntityId())
            ->setLastOrderDate($order->getCreatedAt());
        $this->profileRepository->save($profile);

        return $this->resultFactory->create(['order' => $order]);
    }

    /**
     * Convert profile to order
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @return \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order
     * @throws ScheduledPaymentException
     */
    private function convertProfileToOrder($profile, $paymentPeriod)
    {
        try {
            return $this->converter->convert($profile, $paymentPeriod);
        } catch (\Exception $e) {
            throw new ScheduledPaymentException(__($e->getMessage()), $e);
        }
    }
}
