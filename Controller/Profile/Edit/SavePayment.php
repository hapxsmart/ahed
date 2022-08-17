<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;

/**
 * Class SavePayment
 */
class SavePayment extends AbstractProfile
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param ProfileManagementInterface $profileManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param PaymentInterfaceFactory $paymentFactory
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        ProfileManagementInterface $profileManagement,
        DataObjectHelper $dataObjectHelper,
        PaymentInterfaceFactory $paymentFactory,
        AddressInterfaceFactory $addressFactory
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->profileManagement = $profileManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->paymentFactory = $paymentFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $this->validate($data);
                $profile = $this->performSave($data);
                $this->messageManager->addSuccessMessage(__('Payment has been successfully saved.'));
                return $resultRedirect->setPath('*/*/index', $this->getParams($profile->getProfileId()));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving payment.')
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
        return $this->actionPermission->isEditActionAvailable($profileId);
    }

    /**
     * Validate form
     *
     * @param array $data
     * @return bool
     * @throws LocalizedException
     */
    protected function validate($data)
    {
        if (isset($data['payment']) && !empty($data['payment'])) {
            return true;
        }

        throw new  LocalizedException(__('Data is not correct. Payment information is required.'));
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return ProfileInterface
     * @throws LocalizedException
     * @throws NotFoundException
     */
    protected function performSave($data)
    {
        $profile = $this->getProfile();
        $payment = $this->paymentFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $payment,
            $data['payment'],
            PaymentInterface::class
        );

        $billingAddress = $data['billing_address'] ?? null;

        if ($billingAddress) {
            $snakeCaseBillingAddress = [];
            foreach ($billingAddress as $index => $value) {
                $snakeCaseBillingAddress[SimpleDataObjectConverter::camelCaseToSnakeCase($index)] = $value;
            }

            $snakeCaseBillingAddress['custom_attributes'] = [];
            $address = $this->addressFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $address,
                $snakeCaseBillingAddress,
                AddressInterface::class
            );
            $billingAddress = $address;
        }

        return $this->profileManagement->changePaymentInformation($profile->getProfileId(), $payment, $billingAddress);
    }
}
