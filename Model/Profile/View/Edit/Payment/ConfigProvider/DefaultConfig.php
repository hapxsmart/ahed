<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Aheadworks\Sarp2\Model\UrlBuilder;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\UrlInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Amount as InfoAmount;

/**
 * Class DefaultConfig
 */
class DefaultConfig implements ConfigProviderInterface
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var InfoAmount
     */
    private $infoAmount;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

    /**
     * @var UrlBuilder
     */
    protected $urlParamsBuilder;

    /**
     * @param HttpContext $httpContext
     * @param FormKey $formKey
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param InfoAmount $infoAmount
     * @param RequestInterface $request
     * @param LocaleFormat $localeFormat
     * @param UrlBuilder $urlParamsBuilder
     */
    public function __construct(
        HttpContext $httpContext,
        FormKey $formKey,
        StoreManagerInterface $storeManager,
        Registry $registry,
        UrlInterface $urlBuilder,
        InfoAmount $infoAmount,
        RequestInterface $request,
        LocaleFormat $localeFormat,
        UrlBuilder $urlParamsBuilder
    ) {
        $this->httpContext = $httpContext;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->infoAmount = $infoAmount;
        $this->request = $request;
        $this->localeFormat = $localeFormat;
        $this->urlParamsBuilder = $urlParamsBuilder;
    }

    /**
     * Return configuration array
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        $output['formKey'] = $this->formKey->getFormKey();
        $output['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        $output['isHashUsed'] = (bool)$this->getHash();
        $output['storeCode'] = $store->getCode();
        $output['profileId'] = $this->getProfile()->getProfileId();
        $output['savePaymentUrl'] = $this->getSavePaymentUrl();
        $output['paymentEditMode'] = true;
        $output['quoteData'] = [];
        $output['totalsData'] = [
            'base_grand_total' => $this->infoAmount->getAmount(),
            'base_currency_code' => $store->getBaseCurrencyCode(),
            'quote_currency_code' => $store->getCurrentCurrencyCode()
        ];
        $output['priceFormat'] = $this->localeFormat->getPriceFormat();

        return $output;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Get hash
     *
     * @return bool
     */
    private function getHash()
    {
        return $this->request->getParam(ProfileInterface::HASH);
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    private function getProfile()
    {
        return $this->registry->registry('profile');
    }

    /**
     * Retrieve save payment url
     *
     * @return string
     */
    public function getSavePaymentUrl()
    {
        return $this->urlBuilder->getUrl(
            'aw_sarp2/profile_edit/savePayment',
            $this->urlParamsBuilder->getParams(
                $this->getProfile()->getProfileId(),
                $this->request
            )
        );
    }
}
