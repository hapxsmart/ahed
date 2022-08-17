<?php
namespace Aheadworks\Sarp2\Model\Quote;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Gateway\AbstractTokenAssigner;
use Aheadworks\Sarp2\Model\Payment\Sampler\Adapter\ToOrderPaymentInfoConverter;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Aheadworks\Sarp2\PaymentData\AdapterPool;
use Aheadworks\Sarp2\PaymentData\Payment;
use Aheadworks\Sarp2\PaymentData\PaymentFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment as QuotePayment;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Aheadworks\Sarp2\Model\Payment\SamplerManagement;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class Management
 *
 * @package Aheadworks\Sarp2\Model\Quote
 */
class Management
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var Processor
     */
    private $quoteProcessor;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var AdapterPool
     */
    private $paymentDataAdapterPool;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var SamplerManagement
     */
    private $samplerManagement;

    /**
     * @param HasSubscriptions $quoteChecker
     * @param Processor $quoteProcessor
     * @param CartRepositoryInterface $quoteRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileManagementInterface $profileManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param AdapterPool $paymentDataAdapterPool
     * @param PaymentFactory $paymentFactory
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param Session $checkoutSession
     * @param SamplerManagement $samplerManagement
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        Processor $quoteProcessor,
        CartRepositoryInterface $quoteRepository,
        ProfileRepositoryInterface $profileRepository,
        ProfileManagementInterface $profileManagement,
        OrderRepositoryInterface $orderRepository,
        AdapterPool $paymentDataAdapterPool,
        PaymentFactory $paymentFactory,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        Session $checkoutSession,
        SamplerManagement $samplerManagement
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->quoteProcessor = $quoteProcessor;
        $this->quoteRepository = $quoteRepository;
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->orderRepository = $orderRepository;
        $this->paymentDataAdapterPool = $paymentDataAdapterPool;
        $this->paymentFactory = $paymentFactory;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->checkoutSession = $checkoutSession;
        $this->samplerManagement = $samplerManagement;
    }

    /**
     * Create subscription profiles from quote
     *
     * @param Quote $quote
     * @param Order|int|null $order
     * @return ProfileInterface[]
     * @throws \Exception
     */
    public function createProfiles($quote, $order = null)
    {
        $profiles = [];

        if ($this->quoteChecker->check($quote)) {
            $payment = $quote->getPayment();

            $paymentTokenId = null;
            $paymentToken = null;
            $paymentMethod = null;
            $skipToken = false;

            if ($order !== null && $order->getPayment()->getMethod()!='fac_gateway') {
                $order = is_numeric($order)
                    ? $this->orderRepository->get($order)
                    : $order;
                $additionalInformation = $order->getPayment()->getAdditionalInformation();
                $skipToken = $additionalInformation[AbstractTokenAssigner::SARP_SKIP_PAYMENT_TOKEN] ?? false;

                if ($skipToken) {
                    $paymentTokenId = null;
                    $paymentMethod = $payment->getMethod();
                } elseif ($additionalInformation
                    && isset($additionalInformation[AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID])
                ) {
                    $paymentTokenId = $additionalInformation[AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID];
                    $paymentToken = $this->getPaymentToken($paymentTokenId);
                    $paymentMethod = $paymentToken->getPaymentMethod();
                }
            } else {
                $paymentToken = $this->createPaymentToken($quote, $payment);
                $paymentTokenId = $paymentToken->getTokenId();
                $paymentMethod = $paymentToken->getPaymentMethod();
            }

            if ($paymentToken || $skipToken) {
                $profiles = $this->quoteProcessor->createProfiles($quote);

                $this->saveAndScheduleProfiles(
                    $profiles,
                    $paymentTokenId,
                    $paymentMethod,
                    $order
                );
            }
        }

        return $profiles;
    }

    /**
     * Create subscription profiles from quote using specified payment method
     *
     * @param Quote|CartInterface $quote
     * @param PaymentInterface $payment
     * @param Order|int|null $order
     * @return ProfileInterface[]
     * @throws \Exception
     */
    public function createProfilesUsingPaymentMethod($quote, $payment, $order = null)
    {
        $profiles = [];

        if ($this->quoteChecker->check($quote)) {
            $payment->setQuote($quote);

            if ($order !== null) {
                $order = is_numeric($order)
                    ? $this->orderRepository->get($order)
                    : $order;
                $payment->setData(ToOrderPaymentInfoConverter::CHECKOUT_CREATED_ORDER, $order);
            }

            $profiles = $this->quoteProcessor->createProfiles($quote);
            if (!empty($profiles)) {
                $samplerInfo = $this->samplerManagement->submitPayment($profiles[0], $payment);

                $this->saveAndScheduleProfiles(
                    $profiles,
                    $samplerInfo->getAdditionalInformation(AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID),
                    $samplerInfo->getMethod(),
                    $order
                );

                $this->samplerManagement->updateInfo($samplerInfo, $profiles[0]);
            }
        }

        return $profiles;
    }

    /**
     * Create payment token
     *
     * @param Quote $quote
     * @param QuotePayment $quotePayment
     * @return PaymentTokenInterface
     * @throws \Exception
     */
    private function createPaymentToken($quote, $quotePayment)
    {
        $paymentMethod = $quotePayment->getMethod();
        $paymentDataAdapter = $this->paymentDataAdapterPool->getAdapter($paymentMethod);

        /** @var Payment $paymentDataInfo */
        $paymentDataInfo = $this->paymentFactory->create(
            [
                'paymentInfo' => $quotePayment,
                'quote' => $quote
            ]
        );
        $paymentData = $paymentDataAdapter->create($paymentDataInfo);
        $tokenType = $paymentData->getTokenType();

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setPaymentMethod($paymentMethod)
            ->setType($tokenType)
            ->setTokenValue($paymentData->getGatewayToken())
            ->setIsActive(true);
        if ($tokenType == Token::TOKEN_TYPE_CARD) {
            $paymentToken
                ->setExpiresAt(
                    $paymentData->getAdditionalData()->getData('expiration_date')
                )->setDetails(
                    'type',
                    $paymentData->getAdditionalData()->getData('credit_card_type')
                )->setDetails(
                    'maskedCC',
                    $paymentData->getAdditionalData()->getData('credit_card_masked_number')
                )->setDetails(
                    'expirationDate',
                    $paymentData->getAdditionalData()->getData('credit_card_expiration_date')
                );
        }
        $this->paymentTokenRepository->save($paymentToken);
        return $paymentToken;
    }

    /**
     * Get payment token
     *
     * @param int $paymentTokenId
     * @return PaymentTokenInterface|null
     */
    private function getPaymentToken($paymentTokenId)
    {
        try {
            $paymentToken = $this->paymentTokenRepository->get($paymentTokenId);
        } catch (LocalizedException $e) {
            $paymentToken = null;
        }

        return $paymentToken;
    }

    /**
     * Save and schedule profiles
     *
     * @param ProfileInterface[] $profiles
     * @param int $tokenId
     * @param string $paymentMethodCode
     * @param Order|null $order
     * @throws \Exception
     */
    private function saveAndScheduleProfiles($profiles, $tokenId, $paymentMethodCode, $order = null)
    {
        $profileIds = [];
        foreach ($profiles as $profile) {
            $profile
                ->setPaymentTokenId($tokenId)
                ->setPaymentMethod($paymentMethodCode);
            if ($order instanceof Order) {
                $profile
                    ->setOrder($order)
                    ->setLastOrderId($order->getEntityId())
                    ->setLastOrderDate($order->getCreatedAt());
            }
            $this->profileRepository->save($profile);
            $profileIds[] = $profile->getProfileId();
        }

        $this->profileManagement->schedule($profiles);

        $this->checkoutSession->setLastProfileIds($profileIds);
        $this->checkoutSession->setLastSuccessProfileIds($profileIds);
    }
}
