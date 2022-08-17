<?php
namespace Aheadworks\Sarp2\Model\Checkout\PaymentInfo;

use Aheadworks\Sarp2\Api\GuestPaymentInfoManagementInterface;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Aheadworks\Sarp2\Model\Quote\Management;
use Aheadworks\Sarp2\Model\Quote\PaymentData\FreePaymentMethod;
use Aheadworks\Sarp2\Model\Sales\Order\OrderSender;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestPaymentInfoManagement
 *
 * @package Aheadworks\Sarp2\Model\Checkout\PaymentInfo
 */
class GuestPaymentInfoManagement implements GuestPaymentInfoManagementInterface
{
    /**
     * @var GuestPaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

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
     * @param GuestPaymentInformationManagementInterface $paymentInformationManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param HasSubscriptions $quoteChecker
     * @param CartRepositoryInterface $quoteRepository
     * @param Management $quoteManagement
     * @param FreePaymentMethod $freePaymentMethodData
     * @param ExceptionHandler $exceptionHandler
     * @param ResourceConnection $resourceConnection
     * @param OrderSender $orderSender
     */
    public function __construct(
        GuestPaymentInformationManagementInterface $paymentInformationManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        HasSubscriptions $quoteChecker,
        CartRepositoryInterface $quoteRepository,
        Management $quoteManagement,
        FreePaymentMethod $freePaymentMethodData,
        ExceptionHandler $exceptionHandler,
        ResourceConnection $resourceConnection,
        OrderSender $orderSender
    ) {
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteChecker = $quoteChecker;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->freePaymentMethodData = $freePaymentMethodData;
        $this->exceptionHandler = $exceptionHandler;
        $this->resourceConnection = $resourceConnection;
        $this->orderSender = $orderSender;
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInfoAndSubmitCart(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        try {
            if ($this->isZeroGrandTotalSubscriptionCart($quote)) {
                return $this->processZeroGrandTotalSubscriptionCart($cartId, $email, $paymentMethod, $billingAddress);
            }
            if ($this->quoteChecker->checkHasBoth($quote)) {
                return $this->processMixedCart($cartId, $email, $paymentMethod, $billingAddress);
            } elseif ($this->quoteChecker->checkHasSubscriptionsOnly($quote)) {
                return $this->processSubscriptionCart($cartId, $email, $paymentMethod, $billingAddress);
            }
        } catch (\Exception $e) {
            $this->exceptionHandler->handle($e);
        }
        return false;
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
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processZeroGrandTotalSubscriptionCart($cartId, $email, $paymentMethod, $billingAddress)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        $quote->setData('aw_sarp_allow_free_payment_method', true);
        $connection = $this->resourceConnection->getConnection('sales');
        try {
            $connection->beginTransaction();
            $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                $cartId,
                $email,
                $this->freePaymentMethodData->getDetails($paymentMethod),
                $billingAddress
            );
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
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processMixedCart($cartId, $email, $paymentMethod, $billingAddress)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
            $cartId,
            $email,
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
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface $billingAddress
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function processSubscriptionCart($cartId, $email, $paymentMethod, $billingAddress)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        $this->paymentInformationManagement->savePaymentInformation(
            $cartId,
            $email,
            $paymentMethod,
            $billingAddress
        );
        $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        $this->quoteManagement->createProfiles($quote);

        return true;
    }
}
