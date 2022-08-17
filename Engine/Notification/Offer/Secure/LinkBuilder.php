<?php
namespace Aheadworks\Sarp2\Engine\Notification\Offer\Secure;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Config\Source\SendSecureLinkTo;
use Magento\Customer\Model\Group;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url as FrontendUrl;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class LinkBuilder
{
    /**
     * @var FrontendUrl
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param FrontendUrl $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        FrontendUrl $urlBuilder,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Build extend link url
     *
     * @param ProfileInterface $profile
     * @return string|null
     */
    public function build(ProfileInterface $profile)
    {
        try {
            $store = $this->getStore($profile);
            $link = $this->urlBuilder
                ->setScope($store)
                ->getUrl(
                    'aw_sarp2/profile_edit/index',
                    $this->getParams($profile)
                );
        } catch (NoSuchEntityException $exception) {
            $link = null;
        }

        return $link;
    }

    /**
     * Get params
     *
     * @param ProfileInterface $profile
     * @return array
     */
    private function getParams(ProfileInterface $profile)
    {
        $params = [ProfileInterface::PROFILE_ID => $profile->getProfileId()];

        if ($this->isSecureLinkAvailable($profile)) {
            $params[ProfileInterface::HASH] = $profile->getHash();
        }

        return $params;
    }

    /**
     * Is secure link available
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isSecureLinkAvailable(ProfileInterface $profile)
    {
        return $this->isSecureLinkAvailableForCustomerGroup(
            $profile->getCustomerGroupId(),
            $profile->getStoreId()
        );
    }

    /**
     * Is secure link available for customer group
     *
     * @param int $customerGroup
     * @param int|null $storeId
     * @return bool
     */
    public function isSecureLinkAvailableForCustomerGroup(int $customerGroup, $storeId = null)
    {
        $sendSecureLinkTo = $this->config->getSendSecureLinkTo($storeId);

        return $sendSecureLinkTo == SendSecureLinkTo::ALL_CUSTOMERS
            || ($sendSecureLinkTo == SendSecureLinkTo::GUEST_CUSTOMERS
            && $customerGroup == Group::NOT_LOGGED_IN_ID);
    }

    /**
     * Get store
     *
     * @param ProfileInterface $profile
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(ProfileInterface $profile) {
        $storeId = $profile->getStoreId();

        return $this->storeManager->getStore($storeId);
    }
}
