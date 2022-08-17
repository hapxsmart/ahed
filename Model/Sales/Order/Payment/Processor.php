<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Payment;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as PaymentDataHelper;
use Magento\Payment\Model\Method\Factory as PaymentFactory;
use Magento\Payment\Model\Method\Free;
use Magento\Payment\Model\MethodInterface as PaymentMethodInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;

/**
 * Class Processor
 *
 * @package Aheadworks\Sarp2\Model\Sales\Order\Payment
 */
class Processor
{
    /**
     * @var ProfileFinder
     */
    private $profileFinder;

    /**
     * @var PaymentDataHelper
     */
    private $paymentHelper;

    /**
     * @var PaymentFactory
     */
    private $paymentMethodFactory;

    /**
     * @var SubstituteFreePayment
     */
    private $substituteInstance;

    /**
     * @var PaymentMethodInterface[]
     */
    private $paymentInstanceCache = [];

    /**
     * @param ProfileFinder $profileFinder
     * @param PaymentDataHelper $paymentHelper
     * @param PaymentFactory $paymentMethodFactory
     */
    public function __construct(
        ProfileFinder $profileFinder,
        PaymentDataHelper $paymentHelper,
        PaymentFactory $paymentMethodFactory
    ) {
        $this->profileFinder = $profileFinder;
        $this->paymentHelper = $paymentHelper;
        $this->paymentMethodFactory = $paymentMethodFactory;
    }

    /**
     * Replacement FREE payment method with Substitute free payment method
     *
     * @param OrderPayment|OrderPaymentInterface $paymentInfo
     * @return OrderPayment
     */
    public function replaceFreeMethodInstance($paymentInfo)
    {
        $order = $paymentInfo->getOrder();

        if (!$this->isReplaced($paymentInfo)
            && $paymentInfo->getMethod() == Free::PAYMENT_METHOD_FREE_CODE
            && $order->getGrandTotal() == 0
        ) {
            try {
                foreach ($order->getItems() as $orderItem) {
                    $options = $orderItem->getProductOptions();

                    if ($this->isSubscription($options)) {
                        $profile = $this->getProfile($order->getEntityId());
                        if ($profile) {
                            $profilePaymentTitle = $this->getProfilePaymentTitle($profile);

                            $substituteFreeInstance = $this->getSubstituteInstance();
                            $substituteFreeInstance
                                ->setTitle($profilePaymentTitle)
                                ->setInfoInstance($paymentInfo);

                            $paymentInfo->setMethodInstance($substituteFreeInstance);
                            $paymentInfo->setAdditionalInformation('method_title', $profilePaymentTitle);

                            return $paymentInfo;
                        }
                    }
                }
            } catch (\Exception $exception) {
            }
        }

        return $paymentInfo;
    }

    /**
     * Check if method instance already replaced
     *
     * @param OrderPayment|OrderPaymentInterface $paymentInfo
     * @return bool
     */
    private function isReplaced($paymentInfo)
    {
        $isReplaced = false;
        if ($paymentInfo->hasMethodInstance()) {
            try {
                $paymentInstance = $paymentInfo->getMethodInstance();
                $isReplaced = $paymentInstance instanceof SubstituteFreePayment;
            } catch (\Exception $exception) {
            }
        }

        return $isReplaced;
    }

    /**
     * Check if an order item is a subscription
     *
     * @param array $options
     * @return bool
     */
    private function isSubscription($options)
    {
        if (isset($options['aw_sarp2_subscription_plan'])
            && is_array($options['aw_sarp2_subscription_plan'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve profile payment method title
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws LocalizedException
     */
    private function getProfilePaymentTitle($profile)
    {
        $profilePaymentInstance = $this->getPaymentInstance(
            $profile->getPaymentMethod()
        );

        return $profilePaymentInstance->getTitle();
    }

    /**
     * Retrieve profile by order id
     *
     * @param $orderId
     * @return ProfileInterface|null
     */
    private function getProfile($orderId)
    {
        $profiles = $this->profileFinder->getByOrder($orderId);
        if (count($profiles)) {
            return reset($profiles);
        }

        return null;
    }

    /**
     * Retrieve payment method instance by code
     *
     * @param string $code
     * @return PaymentMethodInterface
     * @throws LocalizedException
     */
    private function getPaymentInstance($code)
    {
        if (!isset($this->paymentInstanceCache[$code])) {
            $this->paymentInstanceCache[$code] = $this->paymentHelper->getMethodInstance($code);
        }

        return $this->paymentInstanceCache[$code];
    }

    /**
     * Retrieve substitute free payment method instance
     *
     * @return SubstituteFreePayment|PaymentMethodInterface
     * @throws LocalizedException
     */
    private function getSubstituteInstance()
    {
        if (!$this->substituteInstance) {
            $this->substituteInstance = $this->paymentMethodFactory->create(
                SubstituteFreePayment::class
            );
        }

        return $this->substituteInstance;
    }
}
