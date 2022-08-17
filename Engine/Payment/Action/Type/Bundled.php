<?php
namespace Aheadworks\Sarp2\Engine\Payment\Action\Type;

use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfo;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoFactory;
use Aheadworks\Sarp2\Model\Profile\ToMergedOrder;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;

/**
 * Class Bundled
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Type
 */
class Bundled implements ActionInterface
{
    /**
     * @var ToMergedOrder
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
     * @param ToMergedOrder $converter
     * @param PlaceOrder $placeOrderService
     * @param PaymentInfoFactory $paymentInfoFactory
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ToMergedOrder $converter,
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
        $paymentsInfo = $this->toPaymentsInfo($payment);
        $order = $this->placeOrderService->place(
            $this->converter->convert($paymentsInfo),
            $paymentsInfo
        );

        foreach ($paymentsInfo as $paymentInfo) {
            $profile = $paymentInfo->getProfile();
            $profile
                ->setOrder($order)
                ->setLastOrderId($order->getEntityId())
                ->setLastOrderDate($order->getCreatedAt());
            $this->profileRepository->save($profile);
        }

        return $this->resultFactory->create(['order' => $order]);
    }

    /**
     * Convert bundled payment instance into payments info
     *
     * @param PaymentInterface $payment
     * @return PaymentInfo[]
     */
    private function toPaymentsInfo(PaymentInterface $payment)
    {
        $paymentsInfo = [];
        foreach ($payment->getChildItems() as $childPayment) {
            $paymentsInfo[] = $this->paymentInfoFactory->create(
                [
                    'profile' => $childPayment->getProfile(),
                    'paymentPeriod' => $childPayment->getPaymentPeriod()
                ]
            );
        }
        return $paymentsInfo;
    }
}
