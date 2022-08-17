<?php
namespace Aheadworks\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class UrlBuilder
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get profile edit index url
     *
     * @param int $profileId
     * @param RequestInterface $request
     * @return string
     */
    public function getProfileEditIndexUrl($profileId, $request = null)
    {
        return $this->urlBuilder->getUrl(
            'aw_sarp2/profile_edit/index',
            $this->getParams($profileId, $request)
        );
    }

    /**
     * Get subscription edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getSubscriptionEditUrl($profileId)
    {
        return $this->urlBuilder->getUrl(
            'aw_sarp2/subscription/view',
            [ProfileInterface::PROFILE_ID => $profileId]
        );
    }

    /**
     * Get params
     *
     * @param int $profileId
     * @param RequestInterface|null $request
     * @param int|null $itemId
     * @return array
     */
    public function getParams($profileId, $request = null, $itemId = null)
    {
        $params = [];
        if ($profileId) {
            $params = array_merge($params, [ProfileInterface::PROFILE_ID => $profileId]);
        }

        if ($itemId) {
            $params = array_merge($params, [ProfileItemInterface::ITEM_ID => $itemId]);
        }

        if ($request) {
            $hash = $request->getParam(ProfileInterface::HASH);
            if ($hash) {
                $params = array_merge($params, [ProfileInterface::HASH => $hash]);
            }
        }

        return $params;
    }
}
