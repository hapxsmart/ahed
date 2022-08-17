<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class SaveAddress
 */
class SaveAddress extends AbstractProfile
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

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
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        FormKeyValidator $formKeyValidator,
        CustomerRepositoryInterface $customerRepository,
        ProfileManagementInterface $profileManagement
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
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
                $this->validate();
                $profile = $this->performSave($data);
                $this->messageManager->addSuccessMessage(__('Shipping Address has been successfully changed.'));
                return $resultRedirect->setPath('*/*/index', $this->getParams($profile->getProfileId()));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while changed the Address.'));
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
        return $this->actionPermission->isEditAddressActionAvailable($profileId);
    }

    /**
     * Validate form
     *
     * @throws LocalizedException
     */
    private function validate()
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
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    private function performSave($data)
    {
        $profile = $this->getProfile();
        $customerAddressId = isset($data['customer_address_id']) ? $data['customer_address_id'] : 0;
        $customer = $this->customerRepository->getById($profile->getCustomerId());
        /** @var AddressInterface $address */
        $address = $this->getAddressById($customer, $customerAddressId);

        return $this->profileManagement->changeShippingAddress($profile->getProfileId(), $address);
    }

    /**
     * Get address by id
     *
     * @param CustomerInterface $customer
     * @param int $addressId
     * @return AddressInterface|null
     * @throws NoSuchEntityException
     */
    private function getAddressById(CustomerInterface $customer, $addressId)
    {
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        throw NoSuchEntityException::singleField('addressId', $addressId);
    }
}
