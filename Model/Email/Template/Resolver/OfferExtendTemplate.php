<?php
namespace Aheadworks\Sarp2\Model\Email\Template\Resolver;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;

/**
 * Class OfferExtendTemplate
 *
 * @package Aheadworks\Sarp2\Model\Email\Template\Resolver
 */
class OfferExtendTemplate implements TemplateResolverInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    /**
     * @inheritDoc
     */
    public function resolve(NotificationInterface $notification)
    {
        $profileId = $notification->getProfileId();
        $profile = $this->profileRepository->get($profileId);

        return $profile->getProfileDefinition()->getOfferExtendEmailTemplate();
    }
}
