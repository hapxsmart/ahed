<?php
namespace Aheadworks\Sarp2\Engine\Profile\Checker;

use Aheadworks\Sarp2\Engine\Profile\Checker\Entities\Data;
use Aheadworks\Sarp2\Engine\Profile\Checker\MergeableInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Config;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile\ShippingMethod;

/**
 * Class MergeAble
 * @package Aheadworks\Sarp2\Engine\Profile\Checker
 */
class MergeAble implements MergeableInterface
{
    /**
     * @var ShippingMethod
     */
    private $shippingMethodResolver;

    /**
     * @var Config
     */
    private $engineConfig;

    /**
     * @var Data
     */
    private $entitesDataChecker;

    /**
     * @param ShippingMethod $shippingMethodResolver
     * @param Config $engineConfig
     * @param Data $entitesDataChecker
     */
    public function __construct(
        ShippingMethod $shippingMethodResolver,
        Config $engineConfig,
        Data $entitesDataChecker
    ) {
        $this->shippingMethodResolver = $shippingMethodResolver;
        $this->engineConfig = $engineConfig;
        $this->entitesDataChecker = $entitesDataChecker;
    }

    /**
     * @inheirtDoc
     */
    public function check(ProfileInterface $profile1, ProfileInterface $profile2)
    {
        if ($profile1->getCustomerIsGuest() || $profile2->getCustomerIsGuest()) {
            return false;
        }
        if ($profile1->getPlanId() || $profile2->getPlanId()) {
            return false;
        }

        if (!$this->entitesDataChecker->checkEntitiesData($profile1, $profile2, ProfileInterface::class)
            || !$this->entitesDataChecker->checkEntitiesData(
                $profile1->getShippingAddress(),
                $profile2->getShippingAddress(),
                ProfileAddressInterface::class
            )
        ) {
            return false;
        }

        $shippingMethod = $this->shippingMethodResolver->getResolvedValue(
            [$profile1, $profile2],
            ProfileInterface::CHECKOUT_SHIPPING_METHOD
        );

        if (!$shippingMethod) {
            return false;
        }

        if (!$this->engineConfig->isVirtualProfilesBundleEnabled()
            && ($profile1->getIsVirtual() || $profile2->getIsVirtual())
        ) {
            return false;
        }

        return true;
    }
}
