<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class RenewButton
 *
 * @package Aheadworks\Sarp2\Block\Adminhtml\Subscription\Edit
 */
class RenewButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var ActionPermission
     */
    private $actionPermission;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ProfileManagementInterface $profileManagement
     * @param ActionPermission $actionPermission
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ProfileManagementInterface $profileManagement,
        ActionPermission $actionPermission
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->profileManagement = $profileManagement;
        $this->actionPermission = $actionPermission;
    }

    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        $data = [];
        $profileId = $this->request->getParam('profile_id');
        if ($profileId) {
            $isRenewActionAllowed = $this->actionPermission->isRenewActionAvailable($profileId);
            $allowedStatuses = $this->profileManagement->getAllowedStatuses($profileId);
            if (in_array(Status::ACTIVE, $allowedStatuses) && $isRenewActionAllowed) {
                $data = [
                    'label' => __('Renew Subscription'),
                    'class' => 'save',
                    'on_click' => sprintf(
                        "deleteConfirm('%s', '%s')",
                        __('Are you sure you want to do this?'),
                        $this->urlBuilder->getUrl('*/*/renew', ['profile_id' => $profileId])
                    ),
                    'sort_order' => 40
                ];
            }
        }
        return $data;
    }
}
