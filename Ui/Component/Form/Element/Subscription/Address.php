<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Element\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Address\Renderer as AddressRenderer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\Config;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Exception\LocalizedException;

class Address extends Field
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @var array
     */
    private $optionsArray = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRenderer $addressRenderer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProfileRepositoryInterface $profileRepository,
        CustomerRepositoryInterface $customerRepository,
        AddressRenderer $addressRenderer,
        array $components = [],
        array $data = []
    ) {
        $this->profileRepository = $profileRepository;
        $this->customerRepository = $customerRepository;
        $this->addressRenderer = $addressRenderer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function prepare()
    {
        $profileId = $this->getContext()->getRequestParam(ProfileInterface::PROFILE_ID);
        $profile = $this->profileRepository->get($profileId);
        $customer = $this->customerRepository->getById($profile->getCustomerId());

        $config = $this->getData('config');
        $options = $this->getAddressOptions($customer);
        $config['options'] = $options;
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * Get address option array
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function getAddressOptions(CustomerInterface $customer)
    {
        if (empty($this->optionsArray)) {
            $this->optionsArray[] = ['value' => null, 'label' => __('Please Select New Address')];
            foreach ($customer->getAddresses() as $address) {
                $this->optionsArray[] = [
                    'value' => $address->getId(),
                    'label' => $this->addressRenderer->render($address, Config::DEFAULT_ADDRESS_FORMAT)
                ];
            }
        }

        return $this->optionsArray;
    }
}
