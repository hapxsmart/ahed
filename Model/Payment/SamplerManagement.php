<?php
namespace Aheadworks\Sarp2\Model\Payment;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Gateway\AbstractTokenAssigner;
use Aheadworks\Sarp2\Model\Payment\Sampler\Adapter\PaymentInfoDataExtractor;
use Aheadworks\Sarp2\Model\Payment\Sampler\Adapter\ToOrderPaymentInfoConverter;
use Aheadworks\Sarp2\Model\Payment\Sampler\Exception\ExceptionWithUnmaskedMessage;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info as SamplerInfo;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Finder as SamplerInfoFinder;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Initialization;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Persistence;
use Aheadworks\Sarp2\Model\Payment\Sampler\InfoFactory;
use Aheadworks\Sarp2\Model\Payment\Sampler\Pool as SamplerPool;
use Aheadworks\Sarp2\Model\Profile\ToQuote;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface as QuotePaymentInfoInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SamplerManagement
 * @package Aheadworks\Sarp2\Model\Payment
 */
class SamplerManagement
{
    /**
     * Maximum revert operations count
     */
    const MAX_REVERT_OPERATIONS_COUNT = 10;

    /**
     * @var SamplerPool
     */
    private $samplerPool;

    /**
     * @var InfoFactory
     */
    private $samplerInfoFactory;

    /**
     * @var Initialization
     */
    private $samplerInfoInitialization;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SamplerInfoFinder
     */
    private $samplerInfoFinder;

    /**
     * @var ToQuote
     */
    private $toQuote;

    /**
     * @var PaymentInfoDataExtractor
     */
    private $paymentDadaExtractor;

    /**
     * @param SamplerPool $samplerPool
     * @param InfoFactory $samplerInfoFactory
     * @param Initialization $samplerInfoInitialization
     * @param Persistence $persistence
     * @param LoggerInterface $logger
     * @param SamplerInfoFinder $samplerInfoFinder
     * @param ToQuote $toQuote
     * @param PaymentInfoDataExtractor $dataExtractor
     */
    public function __construct(
        SamplerPool $samplerPool,
        InfoFactory $samplerInfoFactory,
        Initialization $samplerInfoInitialization,
        Persistence $persistence,
        LoggerInterface $logger,
        SamplerInfoFinder $samplerInfoFinder,
        ToQuote $toQuote,
        PaymentInfoDataExtractor $dataExtractor
    ) {
        $this->samplerPool = $samplerPool;
        $this->samplerInfoFactory = $samplerInfoFactory;
        $this->samplerInfoInitialization = $samplerInfoInitialization;
        $this->persistence = $persistence;
        $this->logger = $logger;
        $this->samplerInfoFinder = $samplerInfoFinder;
        $this->toQuote = $toQuote;
        $this->paymentDadaExtractor = $dataExtractor;
    }

    /**
     * Submit payment
     *
     * @param ProfileInterface $profile
     * @param QuotePaymentInfoInterface $quotePaymentInfo
     * @return SamplerInfo
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function submitPayment($profile, QuotePaymentInfoInterface $quotePaymentInfo)
    {
        $samplerPaymentInfo = $this->createAndInitSamplerInfo($profile, $quotePaymentInfo);

        try {
            $this->persistence->save($samplerPaymentInfo);
            $this->prepareQuotePaymentInfo($quotePaymentInfo, $profile, $samplerPaymentInfo);

            $samplerAdapter = $this->samplerPool->getSampler($quotePaymentInfo->getMethod());

            $paymentData = $this->paymentDadaExtractor->getPaymentDataAndConvertToDataObject($quotePaymentInfo);
            $samplerAdapter->assignData($samplerPaymentInfo, $paymentData);
            $quotePaymentInfo->getMethodInstance()->assignData($paymentData);

            $samplerAdapter->place($samplerPaymentInfo, $quotePaymentInfo);

            $additionalInformation = $samplerPaymentInfo->getAdditionalInformation();
            if (!$additionalInformation
                || ($additionalInformation
                    && !isset($additionalInformation[AbstractTokenAssigner::SARP_PAYMENT_TOKEN_ID])
                    && !isset($additionalInformation[AbstractTokenAssigner::SARP_SKIP_PAYMENT_TOKEN])
                )
            ) {
                throw new LocalizedException(__('Token can\'t be received.'));
            }
            $this->persistence->save($samplerPaymentInfo);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);

            if ($samplerPaymentInfo->getId()) {
                $samplerPaymentInfo->setStatus(SamplerInfoInterface::STATUS_FAILED);
                $this->persistence->save($samplerPaymentInfo);
            }

            $phrase = $exception instanceof ExceptionWithUnmaskedMessage
                ? __($exception->getMessage())
                : __('A server error stopped your payment details from being saved.');

            throw new CouldNotSaveException(
                $phrase,
                $exception
            );
        }

        return $samplerPaymentInfo;
    }

    /**
     * Revert payments
     */
    public function revertPayments()
    {
        $samplerInfoArray = $this->samplerInfoFinder->getSamplePaymentsToRevert(
            self::MAX_REVERT_OPERATIONS_COUNT
        );

        /** @var SamplerInfo $samplerInfo */
        foreach ($samplerInfoArray as $samplerInfo) {
            $this->revertPaymentBySamplerInfo($samplerInfo);
        }
    }

    /**
     * Revert specific payment by its sampler info
     *
     * @param SamplerInfo $samplerInfo
     * @return SamplerInfo|null
     */
    public function revertPaymentBySamplerInfo($samplerInfo)
    {
        try {
            $paymentMethodCode = $samplerInfo->getMethod();
            $sampler = $this->samplerPool->getSampler($paymentMethodCode);
            $sampler->revert($samplerInfo);
            $this->persistence->save($samplerInfo);
            return $samplerInfo;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return null;
        }
    }

    /**
     * Create an init SamplerInfo model
     *
     * @param ProfileInterface $profile
     * @param QuotePaymentInfoInterface $quotePaymentInfo
     * @return SamplerInfo
     */
    private function createAndInitSamplerInfo($profile, $quotePaymentInfo)
    {
        /** @var SamplerInfo $samplerInfo */
        $samplerInfo = $this->samplerInfoFactory->create();
        $this->samplerInfoInitialization->init($samplerInfo, $quotePaymentInfo);
        $samplerInfo
            ->setProfileId($profile->getProfileId())
            ->setProfile($profile);
        if ($order = $quotePaymentInfo->getData(ToOrderPaymentInfoConverter::CHECKOUT_CREATED_ORDER)) {
            $samplerInfo->setOrder($order);
        }

        return $samplerInfo;
    }

    /**
     * Update Profile ID
     *
     * @param SamplerInfo $samplerInfo
     * @param ProfileInterface $profile
     */
    public function updateInfo($samplerInfo, $profile)
    {
        if (!$samplerInfo->getProfileId()) {
            $samplerInfo->setProfileId($profile->getProfileId());
            try {
                $this->persistence->save($samplerInfo);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Prepare quote payment info
     *
     * @param QuotePaymentInfoInterface $quotePaymentInfo
     * @param ProfileInterface $profile
     * @param SamplerInfo $samplerPaymentInfo
     */
    private function prepareQuotePaymentInfo($quotePaymentInfo, $profile, $samplerPaymentInfo)
    {
        if (!$quotePaymentInfo->getQuote()) {
            $quote = $this->toQuote->convert($profile);
            if ($samplerPaymentInfo->getQuoteId()) {
                $quote->setId($samplerPaymentInfo->getQuoteId());
            }
            $quotePaymentInfo->setQuote($quote);
        }
    }
}
