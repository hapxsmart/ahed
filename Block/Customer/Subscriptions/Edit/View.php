<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Block\Customer\Subscription;
use Aheadworks\Sarp2\Engine\Profile\Checker\PaymentToken as TokenActiveChecker;
use Aheadworks\Sarp2\Engine\Profile\Item\Checker\IsRemoveActionAvailable;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver;
use Aheadworks\Sarp2\Model\Profile\Address\Renderer as AddressRenderer;
use Aheadworks\Sarp2\Model\UrlBuilder;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Directory\Model\CurrencyFactory;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\View\Element\Template;

class View extends Subscription
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @var IsRemoveActionAvailable
     */
    private $isRemoveActionAvailable;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProfileManagementInterface $profileManagement
     * @param StatusSource $statusSource
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param CurrencyFactory $currencyFactory
     * @param ActionPermission $actionPermission
     * @param AddressRenderer $addressRenderer
     * @param PlanRepositoryInterface $planRepository
     * @param TitleResolver $titleResolver
     * @param TokenActiveChecker $profileTokenChecker
     * @param UrlBuilder $urlBuilder
     * @param IsRemoveActionAvailable $isRemoveActionAvailable
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProfileManagementInterface $profileManagement,
        StatusSource $statusSource,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        CurrencyFactory $currencyFactory,
        ActionPermission $actionPermission,
        AddressRenderer $addressRenderer,
        PlanRepositoryInterface $planRepository,
        TitleResolver $titleResolver,
        TokenActiveChecker $profileTokenChecker,
        UrlBuilder $urlBuilder,
        IsRemoveActionAvailable $isRemoveActionAvailable,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $profileManagement,
            $statusSource,
            $productRepository,
            $productUrl,
            $currencyFactory,
            $actionPermission,
            $planRepository,
            $titleResolver,
            $profileTokenChecker,
            $urlBuilder,
            $data
        );
        $this->registry = $registry;
        $this->addressRenderer = $addressRenderer;
        $this->isRemoveActionAvailable = $isRemoveActionAvailable;
    }

    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->registry->registry('profile');
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getSubscriptionPlanEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/plan',
            $this->urlBuilder->getParams($profileId, $this->getRequest())
        );
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getNextPaymentDateEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/nextPaymentDate',
            $this->urlBuilder->getParams($profileId, $this->getRequest())
        );
    }

    /**
     * Get shipping address edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getShippingAddressEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/address',
            $this->urlBuilder->getParams($profileId, $this->getRequest())
        );
    }

    /**
     * Get payment details edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getPaymentDetailsEditUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/payment',
            $this->urlBuilder->getParams($profileId, $this->getRequest())
        );
    }

    /**
     * Retrieve string with formatted address
     *
     * @param ProfileAddressInterface $address
     * @return null|string
     */
    public function getFormattedAddress($address)
    {
        return $this->addressRenderer->render($address);
    }

    /**
     * Retrieve payment details html
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getPaymentDetailsHtml($profile)
    {
        $paymentDetailsHtml = '';
        /** @var Template $paymentDetailsTemplate */
        $paymentDetailsTemplate = $this->getChildBlock(
            'aw_sarp2.customer.subscriptions.edit.view.payment.details'
        );
        if ($paymentDetailsTemplate && $paymentDetailsTemplate instanceof Template) {
            $paymentDetailsTemplate->assign('profile', $profile);
            $paymentDetailsHtml = $paymentDetailsTemplate->toHtml();
        }
        return $paymentDetailsHtml;
    }

    /**
     * Retrieve item name
     *
     * @param ProfileItemInterface $item
     * @return string
     */
    public function getItemName($item)
    {
        $name = $item->getName();
        if ($item->getProductType() == Configurable::TYPE_CODE) {
            $name .= ' (' . $item->getSku() . ')';
        }

        return $name;
    }

    /**
     * Check if edit item action available
     *
     * @param ProfileInterface $profile
     * @return bool
     * @throws LocalizedException
     */
    public function isEditItemActionAvailable($profile)
    {
        return $this->actionPermission->isEditProductItemActionAvailable($profile->getProfileId());
    }

    /**
     * Check if remove action available
     *
     * @param ProfileInterface $profile
     * @param ProfileItemInterface $profileItem
     * @return bool
     * @throws LocalizedException
     */
    public function isRemoveActionAvailable(ProfileInterface $profile, ProfileItemInterface $profileItem): bool
    {
        return $this->isRemoveActionAvailable->check($profile, $profileItem);
    }
}
