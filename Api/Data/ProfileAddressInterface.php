<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProfileItemInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileAddressInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ADDRESS_ID = 'address_id';
    const PROFILE_ID = 'profile_id';
    const PROFILE = 'profile';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const ADDRESS_TYPE = 'address_type';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    const QUOTE_ADDRESS_ID = 'quote_address_id';
    const CUSTOMER_ID = 'customer_id';
    const EMAIL = 'email';
    const COUNTRY_ID = 'country_id';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const POSTCODE = 'postcode';
    const STREET = 'street';
    const CITY = 'city';
    const COMPANY = 'company';
    const TELEPHONE = 'telephone';
    const FAX = 'fax';
    const LASTNAME = 'lastname';
    const FIRSTNAME = 'firstname';
    const MIDDLENAME = 'middlename';
    const PREFIX = 'prefix';
    const SUFFIX = 'suffix';
    const WEIGHT = 'weight';
    const REGION_CODE = 'region_code';
    const IS_FREE_SHIPPING = 'is_free_shipping';
    /**#@-*/

    /**
     * Get profile address ID
     *
     * @return int|null
     */
    public function getAddressId();

    /**
     * Set profile address ID
     *
     * @param int $addressId
     * @return $this
     */
    public function setAddressId($addressId);

    /**
     * Get profile ID
     *
     * @return int
     */
    public function getProfileId();

    /**
     * Set profile ID
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get address type
     *
     * @return string
     */
    public function getAddressType();

    /**
     * Set address type
     *
     * @param string $addressType
     * @return $this
     */
    public function setAddressType($addressType);

    /**
     * Get customer address ID
     *
     * @return int|null
     */
    public function getCustomerAddressId();

    /**
     * Set customer address ID
     *
     * @param int $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId);

    /**
     * Get quote address ID
     *
     * @return int
     */
    public function getQuoteAddressId();

    /**
     * Set quote address ID
     *
     * @param int $quoteAddressId
     * @return $this
     */
    public function setQuoteAddressId($quoteAddressId);

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get country ID
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country ID
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Get region ID
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Set region ID
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Get region name
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region name
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Set string
     *
     * @param string[]|string $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this|null
     */
    public function setSuffix($suffix);

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight();

    /**
     * Set weight
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Get free shipping flag
     *
     * @return bool
     */
    public function getIsFreeShipping();

    /**
     * Set free shipping flag
     *
     * @param bool $isFreeShipping
     * @return $this
     */
    public function setIsFreeShipping($isFreeShipping);

    /**
     * Get the region code for the order address
     *
     * @return string|null
     */
    public function getRegionCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileAddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileAddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\ProfileAddressExtensionInterface $extensionAttributes
    );
}
