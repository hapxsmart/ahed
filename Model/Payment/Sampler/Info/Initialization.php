<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Customer\Model\Session;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Quote\Locator as QuoteLocator;

class Initialization
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var QuoteLocator
     */
    private $quoteLocator;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param RemoteAddress $remoteAddress
     * @param QuoteLocator $quoteLocator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Session $customerSession,
        RemoteAddress $remoteAddress,
        QuoteLocator $quoteLocator
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->remoteAddress = $remoteAddress;
        $this->quoteLocator = $quoteLocator;
    }

    /**
     * Initialize sampler info instance
     *
     * @param SamplerInfoInterface $info
     * @param PaymentInterface $payment
     * @return SamplerInfoInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function init(SamplerInfoInterface $info, PaymentInterface $payment)
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $info->setStatus(SamplerInfoInterface::STATUS_PENDING)
            ->setMethod($payment->getMethod())
            ->setStoreId($store->getId())
            ->setCurrencyCode($store->getCurrentCurrencyCode())
            ->setCustomerId($this->customerSession->getCustomerId())
            ->setQuoteId($this->quoteLocator->locateCurrentQuoteId());
        $remoteIp = $this->remoteAddress->getRemoteAddress();
        if ($remoteIp) {
            $info->setRemoteIp($remoteIp);
        }
        return $info;
    }
}
