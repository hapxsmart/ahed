<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $profileId = $this->request->getParam(ProfileInterface::PROFILE_ID);
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->urlBuilder->getUrl(
                '*/subscription/view',
                [ProfileInterface::PROFILE_ID => $profileId])
            ),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
