<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class SavePlan
 */
class SavePlan extends AbstractProfile
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param FormKeyValidator $formKeyValidator
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        FormKeyValidator $formKeyValidator,
        ProfileManagementInterface $profileManagement
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->formKeyValidator = $formKeyValidator;
        $this->profileManagement = $profileManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $this->validate($data);
                $profile = $this->performSave($data);
                $planDefinition = $profile->getPlanDefinition();
                $this->messageManager->addSuccessMessage(
                    $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                        ? __('Installment Plan has been successfully changed.')
                        : __('Subscription Plan has been successfully changed.')
                );
                return $resultRedirect->setPath('*/*/index', $this->getParams($profile->getProfileId()));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $planDefinition = $this->getProfile()->getPlanDefinition();
                $this->messageManager->addExceptionMessage(
                    $e,
                    $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                    ? __('Something went wrong while changed the Installment Plan.')
                    : __('Something went wrong while changed the Subscription Plan.')
                );
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
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
        return $this->actionPermission->isEditPlanActionAvailable($profileId);
    }

    /**
     * Validate form
     *
     * @param array $data
     * @throws LocalizedException
     */
    private function validate($data)
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return ProfileInterface
     * @throws LocalizedException
     * @throws NotFoundException
     */
    private function performSave($data)
    {
        $profile = $this->getProfile();

        return $this->profileManagement
            ->changeSubscriptionPlan($profile->getProfileId(), $data['aw_sarp2_subscription_type']);
    }
}
