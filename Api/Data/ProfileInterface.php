<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProfileInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PROFILE_ID = 'profile_id';
    const INCREMENT_ID = 'increment_id';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STATUS = 'status';
    const PLAN_ID = 'plan_id';
    const PLAN_NAME = 'plan_name';
    const PLAN_DEFINITION = 'plan_definition';
    const PLAN_DEFINITION_ID = 'plan_definition_id';
    const PROFILE_DEFINITION = 'profile_definition';
    const PROFILE_DEFINITION_ID = 'profile_definition_id';
    const HASH = 'hash';
    const START_DATE = 'start_date';
    const IS_VIRTUAL = 'is_virtual';
    const ITEMS = 'items';
    const ITEMS_QTY = 'items_qty';
    const BILLING_ADDRESS = 'billing_address';
    const SHIPPING_ADDRESS = 'shipping_address';
    const ADDRESSES = 'addresses';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_TAX_CLASS_ID = 'customer_tax_class_id';
    const CUSTOMER = 'customer';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const CUSTOMER_FULLNAME = 'customer_fullname';
    const CUSTOMER_PREFIX = 'customer_prefix';
    const CUSTOMER_FIRSTNAME = 'customer_firstname';
    const CUSTOMER_MIDDLENAME = 'customer_middlename';
    const CUSTOMER_LASTNAME = 'customer_lastname';
    const CUSTOMER_SUFFIX = 'customer_suffix';
    const CUSTOMER_DOB = 'customer_dob';
    const CUSTOMER_IS_GUEST = 'customer_is_guest';
    const CUSTOMER_WAS_GUEST = 'customer_was_guest';
    const CHECKOUT_SHIPPING_METHOD = 'checkout_shipping_method';
    const CHECKOUT_SHIPPING_DESCRIPTION = 'checkout_shipping_description';
    const INITIAL_SHIPPING_METHOD = 'initial_shipping_method';
    const INITIAL_SHIPPING_DESCRIPTION = 'initial_shipping_description';
    const TRIAL_SHIPPING_METHOD = 'trial_shipping_method';
    const TRIAL_SHIPPING_DESCRIPTION = 'trial_shipping_description';
    const REGULAR_SHIPPING_METHOD = 'regular_shipping_method';
    const REGULAR_SHIPPING_DESCRIPTION = 'regular_shipping_description';
    const GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const PROFILE_CURRENCY_CODE = 'profile_currency_code';
    const BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    const BASE_TO_PROFILE_RATE = 'base_to_profile_rate';
    const INITIAL_FEE = 'initial_fee';
    const BASE_INITIAL_FEE = 'base_initial_fee';
    const INITIAL_SUBTOTAL = 'initial_subtotal';
    const BASE_INITIAL_SUBTOTAL = 'base_initial_subtotal';
    const INITIAL_SUBTOTAL_INCL_TAX = 'initial_subtotal_incl_tax';
    const BASE_INITIAL_SUBTOTAL_INCL_TAX = 'base_initial_subtotal_incl_tax';
    const INITIAL_TAX_AMOUNT = 'initial_tax_amount';
    const BASE_INITIAL_TAX_AMOUNT = 'base_initial_tax_amount';
    const INITIAL_SHIPPING_AMOUNT = 'initial_shipping_amount';
    const BASE_INITIAL_SHIPPING_AMOUNT = 'base_initial_shipping_amount';
    const INITIAL_SHIPPING_AMOUNT_INCL_TAX = 'initial_shipping_amount_incl_tax';
    const BASE_INITIAL_SHIPPING_AMOUNT_INCL_TAX = 'base_initial_shipping_amount_incl_tax';
    const INITIAL_SHIPPING_TAX_AMOUNT = 'initial_shipping_tax_amount';
    const BASE_SHIPPING_INITIAL_TAX_AMOUNT = 'base_trial_shipping_tax_amount';
    const INITIAL_GRAND_TOTAL = 'initial_grand_total';
    const BASE_INITIAL_GRAND_TOTAL = 'base_initial_grand_total';
    const TRIAL_SUBTOTAL = 'trial_subtotal';
    const BASE_TRIAL_SUBTOTAL = 'base_trial_subtotal';
    const TRIAL_SUBTOTAL_INCL_TAX = 'trial_subtotal_incl_tax';
    const BASE_TRIAL_SUBTOTAL_INCL_TAX = 'base_trial_subtotal_incl_tax';
    const TRIAL_TAX_AMOUNT = 'trial_tax_amount';
    const BASE_TRIAL_TAX_AMOUNT = 'base_trial_tax_amount';
    const TRIAL_SHIPPING_AMOUNT = 'trial_shipping_amount';
    const BASE_TRIAL_SHIPPING_AMOUNT = 'base_trial_shipping_amount';
    const TRIAL_SHIPPING_AMOUNT_INCL_TAX = 'trial_shipping_amount_incl_tax';
    const BASE_TRIAL_SHIPPING_AMOUNT_INCL_TAX = 'base_trial_shipping_amount_incl_tax';
    const TRIAL_SHIPPING_TAX_AMOUNT = 'trial_shipping_tax_amount';
    const BASE_SHIPPING_TRIAL_TAX_AMOUNT = 'base_trial_shipping_tax_amount';
    const TRIAL_GRAND_TOTAL = 'trial_grand_total';
    const BASE_TRIAL_GRAND_TOTAL = 'base_trial_grand_total';
    const REGULAR_SUBTOTAL = 'regular_subtotal';
    const BASE_REGULAR_SUBTOTAL = 'base_regular_subtotal';
    const REGULAR_SUBTOTAL_INCL_TAX = 'regular_subtotal_incl_tax';
    const BASE_REGULAR_SUBTOTAL_INCL_TAX = 'base_regular_subtotal_incl_tax';
    const REGULAR_TAX_AMOUNT = 'regular_tax_amount';
    const BASE_REGULAR_TAX_AMOUNT = 'base_regular_tax_amount';
    const REGULAR_SHIPPING_AMOUNT = 'regular_shipping_amount';
    const BASE_REGULAR_SHIPPING_AMOUNT = 'base_regular_shipping_amount';
    const REGULAR_SHIPPING_AMOUNT_INCL_TAX = 'regular_shipping_amount_incl_tax';
    const BASE_REGULAR_SHIPPING_AMOUNT_INCL_TAX = 'base_regular_shipping_amount_incl_tax';
    const REGULAR_SHIPPING_TAX_AMOUNT = 'regular_shipping_tax_amount';
    const BASE_REGULAR_SHIPPING_TAX_AMOUNT = 'base_regular_shipping_tax_amount';
    const REGULAR_GRAND_TOTAL = 'regular_grand_total';
    const BASE_REGULAR_GRAND_TOTAL = 'base_regular_grand_total';
    const PAYMENT_METHOD = 'payment_method';
    const PAYMENT_TOKEN_ID = 'payment_token_id';
    const LAST_ORDER_ID = 'last_order_id';
    const LAST_ORDER_DATE = 'last_order_date';
    const MEMBERSHIP_ACTIVE_UNTIL_DATE = 'membership_active_until_date';
    const REMOTE_IP = 'remote_ip';
    const PRE_PAYMENT_INFO = 'pre_payment_info';
    /**#@-*/

    /**
     * Get profile ID
     *
     * @return int|null
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
     * Get increment ID
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Set increment ID
     *
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

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
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get plan ID
     *
     * @return int|null
     */
    public function getPlanId();

    /**
     * Set plan ID
     *
     * @param int $planId
     * @return $this
     */
    public function setPlanId($planId);

    /**
     * Get plan name
     *
     * @return string
     */
    public function getPlanName();

    /**
     * Set plan name
     *
     * @param string $planName
     * @return $this
     */
    public function setPlanName($planName);

    /**
     * Get plan definition ID
     *
     * @return int
     */
    public function getPlanDefinitionId();

    /**
     * Set plan definition ID
     *
     * @param int $definitionId
     * @return $this
     */
    public function setPlanDefinitionId($definitionId);

    /**
     * Get plan definition
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface
     */
    public function getPlanDefinition();

    /**
     * Set plan definition
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface $planDefinition
     * @return $this
     */
    public function setPlanDefinition($planDefinition);

    /**
     * Get profile definition ID
     *
     * @return int
     */
    public function getProfileDefinitionId();

    /**
     * Set profile definition ID
     *
     * @param int $definitionId
     * @return $this
     */
    public function setProfileDefinitionId($definitionId);

    /**
     * Get profile definition
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface
     */
    public function getProfileDefinition();

    /**
     * Set profile definition
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface $definition
     * @return $this
     */
    public function setProfileDefinition($definition);

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash();

    /**
     * Set hash
     *
     * @param string $hash
     * @return $this
     */
    public function setHash($hash);

    /**
     * Get start date
     *
     * @return string
     */
    public function getStartDate();

    /**
     * Set start date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * Check if cart is virtual
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVirtual();

    /**
     * Set virtual flag
     *
     * @param bool $isVirtual
     * @return $this
     */
    public function setIsVirtual($isVirtual);

    /**
     * Get profile items
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileItemInterface[]
     */
    public function getItems();

    /**
     * Set profile items
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Get the total quantity of all profile items
     *
     * @return float
     */
    public function getItemsQty();

    /**
     * Set the total quantity of all profile items
     *
     * @param float $itemsQty
     * @return $this
     */
    public function setItemsQty($itemsQty);

    /**
     * Get profile addresses
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface[]
     */
    public function getAddresses();

    /**
     * Set profile addresses
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses($addresses);

    /**
     * Get profile billing address
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface|null
     */
    public function getBillingAddress();

    /**
     * Set profile billing address
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress($billingAddress);

    /**
     * Get shipping address
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface|null
     */
    public function getShippingAddress();

    /**
     * Set shipping address
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileAddressInterface $shippingAddress
     * @return $this
     */
    public function setShippingAddress($shippingAddress);

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
     * Get customer tax class Id
     *
     * @return int|null
     */
    public function getCustomerTaxClassId();

    /**
     * Set customer tax class Id
     *
     * @param int $customerTaxClassId
     * @return $this
     */
    public function setCustomerTaxClassId($customerTaxClassId);

    /**
     * Get customer email
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     *
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get customer group ID
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set customer group ID
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Get customer full name
     *
     * @return string
     */
    public function getCustomerFullname();

    /**
     * Set customer full name
     *
     * @param string $customerFullName
     * @return $this
     */
    public function setCustomerFullname($customerFullName);

    /**
     * Get customer prefix
     *
     * @return string|null
     */
    public function getCustomerPrefix();

    /**
     * Set customer prefix
     *
     * @param string $customerPrefix
     * @return $this
     */
    public function setCustomerPrefix($customerPrefix);

    /**
     * Get customer first name
     *
     * @return string|null
     */
    public function getCustomerFirstname();

    /**
     * Set customer first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setCustomerFirstname($firstname);

    /**
     * Get customer middle name
     *
     * @return string|null
     */
    public function getCustomerMiddlename();

    /**
     * Set customer middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setCustomerMiddlename($middlename);

    /**
     * Get customer last name
     *
     * @return string|null
     */
    public function getCustomerLastname();

    /**
     * Set customer last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setCustomerLastname($lastname);

    /**
     * Get customer suffix
     *
     * @return string|null
     */
    public function getCustomerSuffix();

    /**
     * Set customer suffix
     *
     * @param string $customerSuffix
     * @return $this
     */
    public function setCustomerSuffix($customerSuffix);

    /**
     * Get customer dob
     *
     * @return string|null
     */
    public function getCustomerDob();

    /**
     * Set customer dob
     *
     * @param string $customerDob
     * @return $this
     */
    public function setCustomerDob($customerDob);

    /**
     * Checks if customer IS guest
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCustomerIsGuest();

    /**
     * Set if customer IS guest
     *
     * @param bool $customerIsGuest
     * @return $this
     */
    public function setCustomerIsGuest($customerIsGuest);

    /**
     * Checks if customer WAS guest
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCustomerWasGuest();

    /**
     * Set if customer WAS guest
     *
     * @param bool $customerWasGuest
     * @return $this
     */
    public function setCustomerWasGuest($customerWasGuest);

    /**
     * Get checkout shipping method
     *
     * @return string
     */
    public function getCheckoutShippingMethod();

    /**
     * Set checkout shipping method
     *
     * @param string $checkoutShippingMethod
     * @return $this
     */
    public function setCheckoutShippingMethod($checkoutShippingMethod);

    /**
     * Get checkout shipping description
     *
     * @return string
     */
    public function getCheckoutShippingDescription();

    /**
     * Set checkout shipping description
     *
     * @param string $checkoutShippingDescription
     * @return $this
     */
    public function setCheckoutShippingDescription($checkoutShippingDescription);

    /**
     * Get initial shipping method
     *
     * @return string
     */
    public function getInitialShippingMethod();

    /**
     * Set initial shipping method
     *
     * @param string $initialShippingMethod
     * @return $this
     */
    public function setInitialShippingMethod($initialShippingMethod);

    /**
     * Get initial shipping description
     *
     * @return string
     */
    public function getInitialShippingDescription();

    /**
     * Set initial shipping description
     *
     * @param string $initialShippingDescription
     * @return $this
     */
    public function setInitialShippingDescription($initialShippingDescription);

    /**
     * Get trial shipping method
     *
     * @return string
     */
    public function getTrialShippingMethod();

    /**
     * Set trial shipping method
     *
     * @param string $trialShippingMethod
     * @return $this
     */
    public function setTrialShippingMethod($trialShippingMethod);

    /**
     * Get trial shipping description
     *
     * @return string
     */
    public function getTrialShippingDescription();

    /**
     * Set trial shipping description
     *
     * @param string $trialShippingDescription
     * @return $this
     */
    public function setTrialShippingDescription($trialShippingDescription);

    /**
     * Get regular shipping method
     *
     * @return string
     */
    public function getRegularShippingMethod();

    /**
     * Set regular shipping method
     *
     * @param string $regularShippingMethod
     * @return $this
     */
    public function setRegularShippingMethod($regularShippingMethod);

    /**
     * Get regular shipping description
     *
     * @return string
     */
    public function getRegularShippingDescription();

    /**
     * Set regular shipping description
     *
     * @param string $regularShippingDescription
     * @return $this
     */
    public function setRegularShippingDescription($regularShippingDescription);

    /**
     * Get global currency code
     *
     * @return string
     */
    public function getGlobalCurrencyCode();

    /**
     * Set global currency code
     *
     * @param string $globalCurrencyCode
     * @return $this
     */
    public function setGlobalCurrencyCode($globalCurrencyCode);

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get profile currency code
     *
     * @return string
     */
    public function getProfileCurrencyCode();

    /**
     * Set profile currency code
     *
     * @param string $profileCurrencyCode
     * @return $this
     */
    public function setProfileCurrencyCode($profileCurrencyCode);

    /**
     * Get base to global rate
     *
     * @return float
     */
    public function getBaseToGlobalRate();

    /**
     * Set base to global rate
     *
     * @param float $baseToGlobalRate
     * @return $this
     */
    public function setBaseToGlobalRate($baseToGlobalRate);

    /**
     * Get base to profile rate
     *
     * @return float
     */
    public function getBaseToProfileRate();

    /**
     * Set base to profile rate
     *
     * @param float $baseToProfileRate
     * @return $this
     */
    public function setBaseToProfileRate($baseToProfileRate);

    /**
     * Get initial fee in profile currency
     *
     * @return float
     */
    public function getInitialFee();

    /**
     * Set initial fee in profile currency
     *
     * @param float $initialFee
     * @return $this
     */
    public function setInitialFee($initialFee);

    /**
     * Get initial fee in base currency
     *
     * @return float
     */
    public function getBaseInitialFee();

    /**
     * Set initial fee in base currency
     *
     * @param float $baseInitialFee
     * @return $this
     */
    public function setBaseInitialFee($baseInitialFee);

    /**
     * Get initial subtotal in profile currency
     *
     * @return float
     */
    public function getInitialSubtotal();

    /**
     * Set initial subtotal in profile currency
     *
     * @param float $initialSubtotal
     * @return $this
     */
    public function setInitialSubtotal($initialSubtotal);

    /**
     * Get initial subtotal in base currency
     *
     * @return float
     */
    public function getBaseInitialSubtotal();

    /**
     * Set initial subtotal in base currency
     *
     * @param float $baseInitialSubtotal
     * @return $this
     */
    public function setBaseInitialSubtotal($baseInitialSubtotal);

    /**
     * Get initial subtotal including tax in profile currency
     *
     * @return float
     */
    public function getInitialSubtotalInclTax();

    /**
     * Set initial subtotal including tax in profile currency
     *
     * @param float $initialSubtotalInclTax
     * @return $this
     */
    public function setInitialSubtotalInclTax($initialSubtotalInclTax);

    /**
     * Get initial subtotal including tax in base currency
     *
     * @return float
     */
    public function getBaseInitialSubtotalInclTax();

    /**
     * Set initial subtotal including tax in base currency
     *
     * @param float $baseInitialSubtotalInclTax
     * @return $this
     */
    public function setBaseInitialSubtotalInclTax($baseInitialSubtotalInclTax);

    /**
     * Get initial tax amount in profile currency
     *
     * @return float
     */
    public function getInitialTaxAmount();

    /**
     * Set initial tax amount in profile currency
     *
     * @param float $initialTaxAmount
     * @return $this
     */
    public function setInitialTaxAmount($initialTaxAmount);

    /**
     * Get initial tax amount in base currency
     *
     * @return float
     */
    public function getBaseInitialTaxAmount();

    /**
     * Set initial tax amount in base currency
     *
     * @param float $baseInitialTaxAmount
     * @return $this
     */
    public function setBaseInitialTaxAmount($baseInitialTaxAmount);

    /**
     * Get initial shipping amount in profile currency
     *
     * @return float
     */
    public function getInitialShippingAmount();

    /**
     * Set initial shipping amount in profile currency
     *
     * @param float $initialShippingAmount
     * @return $this
     */
    public function setInitialShippingAmount($initialShippingAmount);

    /**
     * Get initial shipping amount in base currency
     *
     * @return float
     */
    public function getBaseInitialShippingAmount();

    /**
     * Set initial shipping amount in base currency
     *
     * @param float $baseInitialShippingAmount
     * @return $this
     */
    public function setBaseInitialShippingAmount($baseInitialShippingAmount);

    /**
     * Get initial shipping amount including tax in profile currency
     *
     * @return float
     */
    public function getInitialShippingAmountInclTax();

    /**
     * Set initial shipping amount including tax in profile currency
     *
     * @param float $initialShippingAmountInclTax
     * @return $this
     */
    public function setInitialShippingAmountInclTax($initialShippingAmountInclTax);

    /**
     * Get initial shipping amount including tax in base currency
     *
     * @return float
     */
    public function getBaseInitialShippingAmountInclTax();

    /**
     * Set initial shipping amount including tax in base currency
     *
     * @param float $baseInitialShippingAmountInclTax
     * @return $this
     */
    public function setBaseInitialShippingAmountInclTax($baseInitialShippingAmountInclTax);

    /**
     * Get initial shipping tax amount in profile currency
     *
     * @return float
     */
    public function getInitialShippingTaxAmount();

    /**
     * Set initial shipping tax amount in profile currency
     *
     * @param float $initialShippingTaxAmount
     * @return $this
     */
    public function setInitialShippingTaxAmount($initialShippingTaxAmount);

    /**
     * Get initial shipping tax amount in base currency
     *
     * @return float
     */
    public function getBaseInitialShippingTaxAmount();

    /**
     * Set initial shipping tax amount in base currency
     *
     * @param float $baseInitialShippingTaxAmount
     * @return $this
     */
    public function setBaseInitialShippingTaxAmount($baseInitialShippingTaxAmount);

    /**
     * Get initial grand total in profile currency
     *
     * @return float
     */
    public function getInitialGrandTotal();

    /**
     * Set initial grand total in profile currency
     *
     * @param float $initialGrandTotal
     * @return $this
     */
    public function setInitialGrandTotal($initialGrandTotal);

    /**
     * Get initial grand total in base currency
     *
     * @return float
     */
    public function getBaseInitialGrandTotal();

    /**
     * Set initial grand total in base currency
     *
     * @param float $baseInitialGrandTotal
     * @return $this
     */
    public function setBaseInitialGrandTotal($baseInitialGrandTotal);

    /**
     * Get trial subtotal in profile currency
     *
     * @return float
     */
    public function getTrialSubtotal();

    /**
     * Set trial subtotal in profile currency
     *
     * @param float $trialSubtotal
     * @return $this
     */
    public function setTrialSubtotal($trialSubtotal);

    /**
     * Get trial subtotal in base currency
     *
     * @return float
     */
    public function getBaseTrialSubtotal();

    /**
     * Set trial subtotal in base currency
     *
     * @param float $baseTrialSubtotal
     * @return $this
     */
    public function setBaseTrialSubtotal($baseTrialSubtotal);

    /**
     * Get trial subtotal including tax in profile currency
     *
     * @return float
     */
    public function getTrialSubtotalInclTax();

    /**
     * Set trial subtotal including tax in profile currency
     *
     * @param float $trialSubtotalInclTax
     * @return $this
     */
    public function setTrialSubtotalInclTax($trialSubtotalInclTax);

    /**
     * Get trial subtotal including tax in base currency
     *
     * @return float
     */
    public function getBaseTrialSubtotalInclTax();

    /**
     * Set trial subtotal including tax in base currency
     *
     * @param float $baseTrialSubtotalInclTax
     * @return $this
     */
    public function setBaseTrialSubtotalInclTax($baseTrialSubtotalInclTax);

    /**
     * Get trial tax amount in profile currency
     *
     * @return float
     */
    public function getTrialTaxAmount();

    /**
     * Set trial tax amount in profile currency
     *
     * @param float $trialTaxAmount
     * @return $this
     */
    public function setTrialTaxAmount($trialTaxAmount);

    /**
     * Get trial tax amount in base currency
     *
     * @return float
     */
    public function getBaseTrialTaxAmount();

    /**
     * Set trial tax amount in base currency
     *
     * @param float $baseTrialTaxAmount
     * @return $this
     */
    public function setBaseTrialTaxAmount($baseTrialTaxAmount);

    /**
     * Get trial shipping amount in profile currency
     *
     * @return float
     */
    public function getTrialShippingAmount();

    /**
     * Set trial shipping amount in profile currency
     *
     * @param float $trialShippingAmount
     * @return $this
     */
    public function setTrialShippingAmount($trialShippingAmount);

    /**
     * Get trial shipping amount in base currency
     *
     * @return float
     */
    public function getBaseTrialShippingAmount();

    /**
     * Set trial shipping amount in base currency
     *
     * @param float $baseTrialShippingAmount
     * @return $this
     */
    public function setBaseTrialShippingAmount($baseTrialShippingAmount);

    /**
     * Get trial shipping amount including tax in profile currency
     *
     * @return float
     */
    public function getTrialShippingAmountInclTax();

    /**
     * Set trial shipping amount including tax in profile currency
     *
     * @param float $trialShippingAmountInclTax
     * @return $this
     */
    public function setTrialShippingAmountInclTax($trialShippingAmountInclTax);

    /**
     * Get trial shipping amount including tax in base currency
     *
     * @return float
     */
    public function getBaseTrialShippingAmountInclTax();

    /**
     * Set trial shipping amount including tax in base currency
     *
     * @param float $baseTrialShippingAmountInclTax
     * @return $this
     */
    public function setBaseTrialShippingAmountInclTax($baseTrialShippingAmountInclTax);

    /**
     * Get trial shipping tax amount in profile currency
     *
     * @return float
     */
    public function getTrialShippingTaxAmount();

    /**
     * Set trial shipping tax amount in profile currency
     *
     * @param float $trialShippingTaxAmount
     * @return $this
     */
    public function setTrialShippingTaxAmount($trialShippingTaxAmount);

    /**
     * Get trial shipping tax amount in base currency
     *
     * @return float
     */
    public function getBaseTrialShippingTaxAmount();

    /**
     * Set trial shipping tax amount in base currency
     *
     * @param float $baseTrialShippingTaxAmount
     * @return $this
     */
    public function setBaseTrialShippingTaxAmount($baseTrialShippingTaxAmount);

    /**
     * Get trial grand total in profile currency
     *
     * @return float
     */
    public function getTrialGrandTotal();

    /**
     * Set trial grand total in profile currency
     *
     * @param float $trialGrandTotal
     * @return $this
     */
    public function setTrialGrandTotal($trialGrandTotal);

    /**
     * Get trial grand total in base currency
     *
     * @return float
     */
    public function getBaseTrialGrandTotal();

    /**
     * Set trial grand total in base currency
     *
     * @param float $baseTrialGrandTotal
     * @return $this
     */
    public function setBaseTrialGrandTotal($baseTrialGrandTotal);

    /**
     * Get regular subtotal in profile currency
     *
     * @return float
     */
    public function getRegularSubtotal();

    /**
     * Set regular subtotal in profile currency
     *
     * @param float $regularSubtotal
     * @return $this
     */
    public function setRegularSubtotal($regularSubtotal);

    /**
     * Get regular subtotal in base currency
     *
     * @return float
     */
    public function getBaseRegularSubtotal();

    /**
     * Set regular subtotal in base currency
     *
     * @param float $baseRegularSubtotal
     * @return $this
     */
    public function setBaseRegularSubtotal($baseRegularSubtotal);

    /**
     * Get regular subtotal including tax in profile currency
     *
     * @return float
     */
    public function getRegularSubtotalInclTax();

    /**
     * Set regular subtotal including tax in profile currency
     *
     * @param float $regularSubtotalInclTax
     * @return $this
     */
    public function setRegularSubtotalInclTax($regularSubtotalInclTax);

    /**
     * Get regular subtotal including tax in base currency
     *
     * @return float
     */
    public function getBaseRegularSubtotalInclTax();

    /**
     * Set regular subtotal including tax in base currency
     *
     * @param float $baseRegularSubtotalInclTax
     * @return $this
     */
    public function setBaseRegularSubtotalInclTax($baseRegularSubtotalInclTax);

    /**
     * Get regular tax amount in profile currency
     *
     * @return float
     */
    public function getRegularTaxAmount();

    /**
     * Set regular tax amount in profile currency
     *
     * @param float $regularTaxAmount
     * @return $this
     */
    public function setRegularTaxAmount($regularTaxAmount);

    /**
     * Get regular tax amount in base currency
     *
     * @return float
     */
    public function getBaseRegularTaxAmount();

    /**
     * Set regular tax amount in base currency
     *
     * @param float $baseRegularTaxAmount
     * @return $this
     */
    public function setBaseRegularTaxAmount($baseRegularTaxAmount);

    /**
     * Get regular shipping amount in profile currency
     *
     * @return float
     */
    public function getRegularShippingAmount();

    /**
     * Set regular shipping amount in profile currency
     *
     * @param float $regularShippingAmount
     * @return $this
     */
    public function setRegularShippingAmount($regularShippingAmount);

    /**
     * Get regular shipping amount in base currency
     *
     * @return float
     */
    public function getBaseRegularShippingAmount();

    /**
     * Set regular shipping amount in base currency
     *
     * @param float $baseRegularShippingAmount
     * @return $this
     */
    public function setBaseRegularShippingAmount($baseRegularShippingAmount);

    /**
     * Get regular shipping amount including tax in profile currency
     *
     * @return float
     */
    public function getRegularShippingAmountInclTax();

    /**
     * Set regular shipping amount including tax in profile currency
     *
     * @param float $regularShippingAmountInclTax
     * @return $this
     */
    public function setRegularShippingAmountInclTax($regularShippingAmountInclTax);

    /**
     * Get regular shipping amount including tax in base currency
     *
     * @return float
     */
    public function getBaseRegularShippingAmountInclTax();

    /**
     * Set regular shipping amount including tax in base currency
     *
     * @param float $baseRegularShippingAmountInclTax
     * @return $this
     */
    public function setBaseRegularShippingAmountInclTax($baseRegularShippingAmountInclTax);

    /**
     * Get regular shipping tax amount in profile currency
     *
     * @return float
     */
    public function getRegularShippingTaxAmount();

    /**
     * Set regular shipping tax amount in profile currency
     *
     * @param float $regularShippingTaxAmount
     * @return $this
     */
    public function setRegularShippingTaxAmount($regularShippingTaxAmount);

    /**
     * Get regular shipping tax amount in base currency
     *
     * @return float
     */
    public function getBaseRegularShippingTaxAmount();

    /**
     * Set regular shipping tax amount in base currency
     *
     * @param float $baseRegularShippingTaxAmount
     * @return $this
     */
    public function setBaseRegularShippingTaxAmount($baseRegularShippingTaxAmount);

    /**
     * Get regular grand total in profile currency
     *
     * @return float
     */
    public function getRegularGrandTotal();

    /**
     * Set regular grand total in profile currency
     *
     * @param float $regularGrandTotal
     * @return $this
     */
    public function setRegularGrandTotal($regularGrandTotal);

    /**
     * Get regular grand total in base currency
     *
     * @return float
     */
    public function getBaseRegularGrandTotal();

    /**
     * Set regular grand total in base currency
     *
     * @param float $baseRegularGrandTotal
     * @return $this
     */
    public function setBaseRegularGrandTotal($baseRegularGrandTotal);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getPaymentMethod();

    /**
     * Set payment method code
     *
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get payment token Id
     *
     * @return int
     */
    public function getPaymentTokenId();

    /**
     * Set payment token Id
     *
     * @param int $paymentTokenId
     * @return $this
     */
    public function setPaymentTokenId($paymentTokenId);

    /**
     * Get last order ID
     *
     * @return int|null
     */
    public function getLastOrderId();

    /**
     * Set last order ID
     *
     * @param int $lastOrderId
     * @return $this
     */
    public function setLastOrderId($lastOrderId);

    /**
     * Get last order date
     *
     * @return string|null
     */
    public function getLastOrderDate();

    /**
     * Set last order date
     *
     * @param string $lastOrderDate
     * @return $this
     */
    public function setLastOrderDate($lastOrderDate);

    /**
     * Get membership active until date
     *
     * @return string|null
     */
    public function getMembershipActiveUntilDate();

    /**
     * Set membership active until date
     *
     * @param string $membershipActiveUntil
     * @return $this
     */
    public function setMembershipActiveUntilDate($membershipActiveUntil);

    /**
     * Get remote IP address
     *
     * @return string
     */
    public function getRemoteIp();

    /**
     * Set remote IP address
     *
     * @param string $remoteIp
     * @return $this
     */
    public function setRemoteIp($remoteIp);

    /**
     * Get profile prepayment info
     *
     * @return \Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface|null
     */
    public function getPrePaymentInfo();

    /**
     * Set profile prepayment info
     *
     * @param \Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface $prePaymentInfo
     * @return $this
     */
    public function setPrePaymentInfo($prePaymentInfo);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\ProfileExtensionInterface $extensionAttributes
    );
}
