<?php
namespace Aheadworks\Sarp2\Model\Profile\View;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\UrlBuilder;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;

class LinkRenderer
{
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param UrlBuilder $urlBuilder
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        UrlBuilder $urlBuilder,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Return html link to subscription view page
     *
     * @param int $profileId
     * @return string
     * @throws LocalizedException
     */
    public function renderSubscriptionViewLinkHtml($profileId)
    {
        $profile = $this->profileRepository->get($profileId);
        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $this->urlBuilder->getSubscriptionEditUrl($profileId),
            $profile->getIncrementId()
        );
    }
}
