<?php
namespace Aheadworks\Sarp2\Model\Checkout\PaymentInfo;

use Aheadworks\Sarp2\Api\PaymentInfoManagementInterface;
use Aheadworks\Sarp2\Model\Checkout\PaymentDetailsProvider;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Aheadworks\Sarp2\Model\Quote\Management;
use Aheadworks\Sarp2\Model\Quote\PaymentData\FreePaymentMethod;
use Aheadworks\Sarp2\Model\Sales\Order\OrderSender;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;

/**
 * Class PaymentInfoManagement
 *
 * @package Aheadworks\Sarp2\Model\Checkout\PaymentInfo
 */
class PaymentInfoManagement implements PaymentInfoManagementInterface
{
    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Management
     */
    private $quoteManagement;

    /**
     * @var PaymentDetailsProvider
     */
    private $paymentDetailsProvider;

    /**
     * @var FreePaymentMethod
     */
    private $freePaymentMethodData;

    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @param PaymentInformationManagementInterface $paymentInformationManagement
     * @param HasSubscriptions $quoteChecker
     * @param CartRepositoryInterface $quoteRepository
     * @param Management $quoteManagement
     * @param PaymentDetailsProvider $paymentDetailsProvider
     * @param FreePaymentMethod $freePaymentMethodData
     * @param ExceptionHandler $exceptionHandler
     * @param ResourceConnection $resourceConnection
     * @param OrderSender $orderSender
     */
    public function __construct(
        PaymentInformationManagementInterface $paymentInformationManagement,
        HasSubscriptions $quoteChecker,
        CartRepositoryInterface $quoteRepository,
        Management $quoteManagement,
        PaymentDetailsProvider $paymentDetailsProvider,
        FreePaymentMethod $freePaymentMethodData,
        ExceptionHandler $exceptionHandler,
        ResourceConnection $resourceConnection,
        OrderSender $orderSender
    ) {
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteChecker = $quoteChecker;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->paymentDetailsProvider = $paymentDetailsProvider;
        $this->freePaymentMethodData = $freePaymentMethodData;
        $this->exceptionHandler = $exceptionHandler;
        $this->resourceConnection = $resourceConnection;
        $this->orderSender = $orderSender;
    }

    /**
     * {@inheritdoc}
     */
    public function savePaymentInfoAndSubmitCart(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        try {
            if ($this->isZeroGrandTotalSubscriptionCart($quote)) {
                return $this->processZeroGrandTotalSubscriptionCart($cartId, $paymentMethod, $billingAddress);
            }
            if ($this->quoteChecker->checkHasBoth($quote)) {
                return $this->processMixedCart($cartId, $paymentMethod, $billingAddress);
            } elseif ($this->quoteChecker->checkHasSubscriptionsOnly($quote)) {
                return $this->processSubscriptionCart($cartId, $paymentMethod, $billingAddress);
            }
        } catch (\Exception $e) {
            $this->exceptionHandler->handle($e);
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentInfoForSubscription()
    {
        return $this->paymentDetailsProvider->getPaymentInformation();
    }

    /**
     * Check if cart has subscription products and grand total is zero
     *
     * @param Quote $quote
     * @return bool
     */
    private function isZeroGrandTotalSubscriptionCart($quote)
    {
        return ($quote->getGrandTotal() <= 0)
            && ($this->quoteChecker->checkHasBoth($quote)
                || $this->quoteChecker->checkHasSubscriptionsOnly($quote));
    }

    /**
     * Process subscription cart with zero grand total
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processZeroGrandTotalSubscriptionCart($cartId, $paymentMethod, $billingAddress)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setData('aw_sarp_allow_free_payment_method', true);
        $connection = $this->resourceConnection->getConnection('sales');
        try {
            $connection->beginTransaction();
            $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                $cartId,
                $this->freePaymentMethodData->getDetails($paymentMethod),
                $billingAddress
            );
            /** @var Quote $quote */
            $quote = $this->quoteRepository->get($cartId);
            $this->quoteManagement->createProfilesUsingPaymentMethod($quote, $paymentMethod, $orderId);
            $this->orderSender->send($orderId);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Process cart with mixed products
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processMixedCart($cartId, $paymentMethod, $billingAddress)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
            $cartId,
            $paymentMethod,
            $billingAddress
        );
        $this->quoteManagement->createProfiles($quote, $orderId);
        return true;
    }

    /**
     * Process subscription cart
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processSubscriptionCart($cartId, $paymentMethod, $billingAddress)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->paymentInformationManagement->savePaymentInformation(
            $cartId,
            $paymentMethod,
            $billingAddress
        );
        $this->quoteManagement->createProfiles($quote);

        return true;
    }
}
