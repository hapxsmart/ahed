<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressExtensionInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Address as AddressResource;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Directory\Model\RegionFactory;

/**
 * Class Address
 * @package Aheadworks\Sarp2\Model\Profile
 */
class Address extends AbstractModel implements ProfileAddressInterface, AddressModelInterface
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param RegionFactory $regionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RegionFactory $regionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->regionFactory = $regionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(AddressResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressId()
    {
        return $this->getData(self::ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressId($addressId)
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->getData(self::PROFILE);
    }

    /**
     * Set profile
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        return $this->setData('profile', $profile);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressType()
    {
        return $this->getData(self::ADDRESS_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddressType($addressType)
    {
        return $this->setData(self::ADDRESS_TYPE, $addressType);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressId()
    {
        return $this->getData(self::CUSTOMER_ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerAddressId($customerAddressId)
    {
        return $this->setData(self::CUSTOMER_ADDRESS_ID, $customerAddressId);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteAddressId()
    {
        return $this->getData(self::QUOTE_ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteAddressId($quoteAddressId)
    {
        return $this->setData(self::QUOTE_ADDRESS_ID, $quoteAddressId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * {@inheritdoc}
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->getData(self::COMPANY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        return $this->setData(self::COMPANY, $company);
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * {@inheritdoc}
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlename()
    {
        return $this->getData(self::MIDDLENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->getData(self::PREFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuffix()
    {
        return $this->getData(self::SUFFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsFreeShipping()
    {
        return $this->getData(self::IS_FREE_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsFreeShipping($isFreeShipping)
    {
        return $this->setData(self::IS_FREE_SHIPPING, $isFreeShipping);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ProfileAddressExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionCode()
    {
        $regionId = (!$this->getRegionId() && is_numeric($this->getRegion())) ?
            $this->getRegion() :
            $this->getRegionId();
        $model = $this->regionFactory->create()->load($regionId);
        if ($model->getCountryId() == $this->getCountryId()) {
            return $model->getCode();
        } elseif (is_string($this->getRegion())) {
            return $this->getRegion();
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreetLine($number)
    {
        $lines = $this->getStreet();
        return $lines[$number - 1] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function explodeStreetAddress()
    {
        // todo: M2SARP-382
    }
}
