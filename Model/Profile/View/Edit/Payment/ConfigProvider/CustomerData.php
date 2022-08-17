<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerData
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider
 */
class CustomerData implements ConfigProviderInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressMapper
     */
    private $addressMapper;

    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressMapper $addressMapper
     * @param AddressConfig $addressConfig
     * @param Registry $registry
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        CustomerRepositoryInterface $customerRepository,
        AddressMapper $addressMapper,
        AddressConfig $addressConfig,
        Registry $registry
    ) {
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Return configuration array
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $output['customerData'] = $this->getCustomerData();
        return $output;
    }

    /**
     * Retrieve customer data
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerData()
    {
        $customerData = [];
        $profile = $this->getProfile();

        if ($profile && $profile->getCustomerId()) {
            $customer = $this->customerRepository->getById($profile->getCustomerId());
            $customerData = $this->dataObjectProcessor->buildOutputDataArray(
                $customer,
                CustomerInterface::class
            );
            foreach ($customer->getAddresses() as $key => $address) {
                $customerData['addresses'][$key]['inline'] = $this->getCustomerAddressInline($address);
            }
        }
        return $customerData;
    }

    /**
     * Set additional customer address data
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    private function getCustomerAddressInline($address)
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    private function getProfile()
    {
        return $this->registry->registry('profile');
    }
}
