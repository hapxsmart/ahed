<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Extend
 *
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Subscription
 */
class Extend extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        ProfileManagementInterface $profileManagement
    ) {
        parent::__construct($context);
        $this->profileManagement = $profileManagement;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($profileId) {
            try {
                $this->profileManagement->extend($profileId);
                $this->messageManager->addSuccessMessage(__('The subscription was successfully extended'));
                return $resultRedirect->setPath('*/subscription/view/', ['profile_id' => $profileId]);
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while renew the subscription.')
                );
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
