<?php
namespace Aheadworks\Sarp2\Model\Email\Sender\Enabler;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Email\Sender\EnablerInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\Definition\ValueResolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class UpcomingBilling
 * @package Aheadworks\Sarp2\Model\Email\Sender\Enabler
 */
class UpcomingBilling implements EnablerInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ValueResolver
     */
    private $definitionValueResolver;

    /**
     * @param Config $config
     * @param ProfileRepositoryInterface $profileRepository
     * @param ValueResolver $definitionValueResolver
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ValueResolver $definitionValueResolver
    ) {
        $this->profileRepository = $profileRepository;
        $this->definitionValueResolver = $definitionValueResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LocalizedException
     */
    public function isEnabled($notification)
    {
        $storeId = $notification->getStoreId();
        $profile = $this->profileRepository->get($notification->getProfileId());
        $profileDefinition = $profile->getProfileDefinition();

        $offset = $this->definitionValueResolver->getUpcomingEmailOffset($profileDefinition, $storeId);

        return $offset > 0;
    }
}
