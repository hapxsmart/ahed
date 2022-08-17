<?php
namespace Aheadworks\Sarp2\Controller\Profile;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class Renew
 *
 * @package Aheadworks\Sarp2\Controller\Profile
 */
class Renew extends AbstractProfile
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProfileRepositoryInterface $profileRepository
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ProfileRepositoryInterface $profileRepository,
        Registry $registry,
        ActionPermission $actionPermission,
        ProfileManagementInterface $profileManagement
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->profileManagement = $profileManagement;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $profile = $this->getProfile();
            $profileId = $profile->getProfileId();
            if ($profile->getCustomerId() == $this->customerSession->getCustomerId()) {
                $this->profileManagement->renew($profileId);
            }
            $this->messageManager->addSuccessMessage(__('The subscription was successfully renewed.'));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while renew the subscription.')
            );
        }
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isRenewActionAvailable($profileId);
    }
}
