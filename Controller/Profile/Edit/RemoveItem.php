<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class RemoveItem
 */
class RemoveItem extends AbstractProfile
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
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $profile = $this->getProfile();
            $profileId = $profile->getProfileId();
            $itemId = (int)$this->getRequest()->getParam(ProfileItemInterface::ITEM_ID);

            if ($profile->getCustomerId() == $this->customerSession->getCustomerId()
                || $profile->getHash() == $this->getRequest()->getParam(ProfileInterface::HASH)
            ) {
                $this->profileManagement->removeItem($profileId, $itemId);
            }
            $this->messageManager
                ->addSuccessMessage(__('The product was successfully removed from this subscription.'));
            return $resultRedirect->setPath('*/*/index', $this->getParams($profileId));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while remove product.')
            );
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isCancelStatusAvailable($profileId);
    }
}
