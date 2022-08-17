<?php
namespace Aheadworks\Sarp2\Block\Checkout\Onepage;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Aheadworks\Sarp2\Engine\Notification\Offer\Secure\LinkBuilder as SecureLinkBuilder;
use Aheadworks\Sarp2\Model\UrlBuilder;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Group;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;

/**
 * Class Success
 *
 * @method bool getCanViewProfiles()
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var SecureLinkBuilder
     */
    private $secureLinkBuilder;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param ProfileRepositoryInterface $profileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlBuilder $urlBuilder
     * @param SecureLinkBuilder $secureLinkBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        ProfileRepositoryInterface $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UrlBuilder $urlBuilder,
        SecureLinkBuilder $secureLinkBuilder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->profileRepository = $profileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->secureLinkBuilder = $secureLinkBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getIncrementId()) {
            parent::prepareBlockData();
        }
        $canViewProfiles = $this->httpContext->getValue('customer_logged_in')
            ? true
            : $this->secureLinkBuilder->isSecureLinkAvailableForCustomerGroup(Group::NOT_LOGGED_IN_ID);
        $this->addData(['can_view_profiles' => $canViewProfiles]);
    }

    /**
     * Get profiles
     *
     * @return ProfileInterface[]
     */
    public function getProfiles()
    {
        $profiles = [];
        $profileIds = $this->_checkoutSession->getLastProfileIds();
        if ($profileIds) {
            $this->_checkoutSession->setLastProfileIds(null);
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(ProfileInterface::PROFILE_ID, $profileIds, 'in')
                ->create();
            $profiles = $this->profileRepository->getList($searchCriteria)
                ->getItems();
        }
        return $profiles;
    }

    /**
     * Retrieve phrase
     *
     * @param ProfileInterface[] $profiles
     * @return \Magento\Framework\Phrase
     */
    public function getPhrase($profiles)
    {
        if (count($profiles) > 1) {
            return __('Your payment plan profiles are');
        } else {
            /** @var ProfileInterface $profile */
            $profile = reset($profiles);
            $planDefinition = $profile->getPlanDefinition();
            return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                ? __('Your payment plan profile is')
                : __('Your subscription profile is');
        }
    }

    /**
     * Get view profile url
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getViewProfileUrl($profile)
    {
        return $this->httpContext->getValue('customer_logged_in')
            ? $this->urlBuilder->getProfileEditIndexUrl($profile->getProfileId())
            : $this->secureLinkBuilder->build($profile);
    }
}
