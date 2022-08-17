<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Helper\Validator\DateValidator;
use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class SaveNextPaymentDate
 */
class SaveNextPaymentDate extends AbstractProfile
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
     * @var FormatConverter
     */
    private $dateFormatConverter;

    /**
     * @var DateValidator
     */
    private $dateValidator;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param FormKeyValidator $formKeyValidator
     * @param ProfileManagementInterface $profileManagement
     * @param FormatConverter $dateFormatConverter
     * @param DateValidator $dateValidator
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        FormKeyValidator $formKeyValidator,
        ProfileManagementInterface $profileManagement,
        FormatConverter $dateFormatConverter,
        DateValidator $dateValidator
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->formKeyValidator = $formKeyValidator;
        $this->profileManagement = $profileManagement;
        $this->dateFormatConverter = $dateFormatConverter;
        $this->dateValidator = $dateValidator;
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
                $this->messageManager->addSuccessMessage(__('Next Payment Date has been successfully changed.'));
                return $resultRedirect->setPath('*/*/index', $this->getParams($profile->getProfileId()));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while changed the Next Payment Date.')
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
        return $this->actionPermission->isEditNextPaymentDateActionAvailable($profileId);
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
        $nextPaymentDate = $data['next-payment-date'];
        $format = $this->dateFormatConverter->convertToDateTimeFormat();
        if (!$this->dateValidator->isValid($nextPaymentDate, $format)) {
            throw new  LocalizedException(__('Next Payment Date is incorrect.'));
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
        $nextPaymentDate = $data['next-payment-date'];
        $newNextPaymentDate = $this->dateFormatConverter->reformat(
            $nextPaymentDate, $this->dateFormatConverter->convertToDateTimeFormat()
        );

        return $this->profileManagement->changeNextPaymentDate($profile->getProfileId(), $newNextPaymentDate);
    }
}
