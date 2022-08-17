<?php
namespace Aheadworks\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\ProfileExtensionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Item\Finder as ItemsFinder;
use Aheadworks\Sarp2\Model\Profile\Validator;
use Aheadworks\Sarp2\Model\Profile\ValidatorFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Address\Collection as AddressCollection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Profile
 */
class Profile extends AbstractModel implements ProfileInterface
{
    /**
     * Entity type
     */
    const ENTITY = 'aw_sarp2_profile';

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var ItemsFinder
     */
    private $itemsFinder;

    /**
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ValidatorFactory $validatorFactory
     * @param ItemsFinder $itemsFinder
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ValidatorFactory $validatorFactory,
        ItemsFinder $itemsFinder,
        AddressCollectionFactory $addressCollectionFactory,
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
        $this->validatorFactory = $validatorFactory;
        $this->itemsFinder = $itemsFinder;
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ProfileResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return (int)$this->getData(self::PROFILE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
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
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanId()
    {
        return $this->getData(self::PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanId($planId)
    {
        return $this->setData(self::PLAN_ID, $planId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanName()
    {
        return $this->getData(self::PLAN_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanName($planName)
    {
        return $this->setData(self::PLAN_NAME, $planName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanDefinitionId()
    {
        return $this->getData(self::PLAN_DEFINITION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanDefinitionId($definitionId)
    {
        return $this->setData(self::PLAN_DEFINITION_ID, $definitionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanDefinition()
    {
        if ($this->getData(self::PLAN_DEFINITION) === null) {
            $this->setData(
                self::PLAN_DEFINITION,
                $this->getResource()->loadDefinition($this->getPlanDefinitionId())
            );
        }
        return $this->getData(self::PLAN_DEFINITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanDefinition($planDefinition)
    {
        return $this->setData(self::PLAN_DEFINITION, $planDefinition);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileDefinitionId()
    {
        return $this->getData(self::PROFILE_DEFINITION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileDefinitionId($definitionId)
    {
        return $this->setData(self::PROFILE_DEFINITION_ID, $definitionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileDefinition()
    {
        if ($this->getData(self::PROFILE_DEFINITION) === null) {
            $this->setData(
                self::PROFILE_DEFINITION,
                $this->getResource()->loadDefinition($this->getProfileDefinitionId(), false)
            );
        }
        return $this->getData(self::PROFILE_DEFINITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileDefinition($definition)
    {
        return $this->setData(self::PROFILE_DEFINITION, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->getData(self::HASH);
    }

    /**
     * {@inheritdoc}
     */
    public function setHash($hash)
    {
        return $this->setData(self::HASH, $hash);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVirtual()
    {
        return $this->getData(self::IS_VIRTUAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVirtual($isVirtual)
    {
        return $this->setData(self::IS_VIRTUAL, $isVirtual);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if ($this->getData(self::ITEMS) === null) {
            $this->setData(
                self::ITEMS,
                $this->itemsFinder->getItemsWithoutHiddenReplaced($this)
            );
        }
        return $this->getData(self::ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsQty()
    {
        return $this->getData(self::ITEMS_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemsQty($itemsQty)
    {
        return $this->setData(self::ITEMS_QTY, $itemsQty);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddresses()
    {
        if ($this->getData(self::ADDRESSES) === null) {
            /** @var AddressCollection $collection */
            $collection = $this->addressCollectionFactory->create();
            $collection->addProfileFilter($this)
                ->walk('setProfile', [$this]);
            $this->setData(self::ADDRESSES, $collection->getItems());
        }
        return $this->getData(self::ADDRESSES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddresses($addresses)
    {
        return $this->setData(self::ADDRESSES, $addresses);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        if ($this->getData(self::BILLING_ADDRESS) === null) {
            foreach ($this->getAddresses() as $address) {
                if ($address->getAddressType() == 'billing') {
                    $this->setData(self::BILLING_ADDRESS, $address);
                }
            }
        }
        return $this->getData(self::BILLING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddress($billingAddress)
    {
        return $this->setData(self::BILLING_ADDRESS, $billingAddress);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        if ($this->getData(self::SHIPPING_ADDRESS) === null) {
            foreach ($this->getAddresses() as $address) {
                if ($address->getAddressType() == 'shipping') {
                    $this->setData(self::SHIPPING_ADDRESS, $address);
                }
            }
        }
        return $this->getData(self::SHIPPING_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddress($shippingAddress)
    {
        return $this->setData(self::SHIPPING_ADDRESS, $shippingAddress);
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
    public function getCustomerTaxClassId()
    {
        return $this->getData(self::CUSTOMER_TAX_CLASS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerTaxClassId($customerTaxClassId)
    {
        return $this->setData(self::CUSTOMER_TAX_CLASS_ID, $customerTaxClassId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerFullname()
    {
        return $this->getData(self::CUSTOMER_FULLNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerFullname($customerFullName)
    {
        return $this->setData(self::CUSTOMER_FULLNAME, $customerFullName);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerPrefix()
    {
        return $this->getData(self::CUSTOMER_PREFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerPrefix($customerPrefix)
    {
        return $this->setData(self::CUSTOMER_PREFIX, $customerPrefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerFirstname()
    {
        return $this->getData(self::CUSTOMER_FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerFirstname($firstname)
    {
        return $this->setData(self::CUSTOMER_FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMiddlename()
    {
        return $this->getData(self::CUSTOMER_MIDDLENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerMiddlename($middlename)
    {
        return $this->setData(self::CUSTOMER_MIDDLENAME, $middlename);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerLastname()
    {
        return $this->getData(self::CUSTOMER_LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerLastname($lastname)
    {
        return $this->setData(self::CUSTOMER_LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerSuffix()
    {
        return $this->getData(self::CUSTOMER_SUFFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerSuffix($customerSuffix)
    {
        return $this->setData(self::CUSTOMER_SUFFIX, $customerSuffix);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerDob()
    {
        return $this->getData(self::CUSTOMER_DOB);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerDob($customerDob)
    {
        return $this->setData(self::CUSTOMER_DOB, $customerDob);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerIsGuest()
    {
        return $this->getData(self::CUSTOMER_IS_GUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerIsGuest($customerIsGuest)
    {
        return $this->setData(self::CUSTOMER_IS_GUEST, $customerIsGuest);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerWasGuest()
    {
        return $this->getData(self::CUSTOMER_WAS_GUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerWasGuest($customerWasGuest)
    {
        return $this->setData(self::CUSTOMER_WAS_GUEST, $customerWasGuest);
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutShippingMethod()
    {
        return $this->getData(self::CHECKOUT_SHIPPING_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setCheckoutShippingMethod($checkoutShippingMethod)
    {
        return $this->setData(self::CHECKOUT_SHIPPING_METHOD, $checkoutShippingMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutShippingDescription()
    {
        return $this->getData(self::CHECKOUT_SHIPPING_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setCheckoutShippingDescription($checkoutShippingDescription)
    {
        return $this->setData(self::CHECKOUT_SHIPPING_DESCRIPTION, $checkoutShippingDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialShippingMethod()
    {
        return $this->getData(self::INITIAL_SHIPPING_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialShippingMethod($initialShippingMethod)
    {
        return $this->setData(self::INITIAL_SHIPPING_METHOD, $initialShippingMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialShippingDescription()
    {
        return $this->getData(self::INITIAL_SHIPPING_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialShippingDescription($initialShippingDescription)
    {
        return $this->setData(self::INITIAL_SHIPPING_DESCRIPTION, $initialShippingDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialShippingMethod()
    {
        return $this->getData(self::TRIAL_SHIPPING_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialShippingMethod($trialShippingMethod)
    {
        return $this->setData(self::TRIAL_SHIPPING_METHOD, $trialShippingMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialShippingDescription()
    {
        return $this->getData(self::TRIAL_SHIPPING_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialShippingDescription($trialShippingDescription)
    {
        return $this->setData(self::TRIAL_SHIPPING_DESCRIPTION, $trialShippingDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularShippingMethod()
    {
        return $this->getData(self::REGULAR_SHIPPING_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularShippingMethod($regularShippingMethod)
    {
        return $this->setData(self::REGULAR_SHIPPING_METHOD, $regularShippingMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularShippingDescription()
    {
        return $this->getData(self::REGULAR_SHIPPING_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularShippingDescription($regularShippingDescription)
    {
        return $this->setData(self::REGULAR_SHIPPING_DESCRIPTION, $regularShippingDescription);
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalCurrencyCode()
    {
        return $this->getData(self::GLOBAL_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setGlobalCurrencyCode($globalCurrencyCode)
    {
        return $this->setData(self::GLOBAL_CURRENCY_CODE, $globalCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::BASE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileCurrencyCode()
    {
        return $this->getData(self::PROFILE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileCurrencyCode($profileCurrencyCode)
    {
        return $this->setData(self::PROFILE_CURRENCY_CODE, $profileCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseToGlobalRate()
    {
        return $this->getData(self::BASE_TO_GLOBAL_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToGlobalRate($baseToGlobalRate)
    {
        return $this->setData(self::BASE_TO_GLOBAL_RATE, $baseToGlobalRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseToProfileRate()
    {
        return $this->getData(self::BASE_TO_PROFILE_RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToProfileRate($baseToProfileRate)
    {
        return $this->setData(self::BASE_TO_PROFILE_RATE, $baseToProfileRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialFee()
    {
        return $this->getData(self::INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialFee($initialFee)
    {
        return $this->setData(self::INITIAL_FEE, $initialFee);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialFee()
    {
        return $this->getData(self::BASE_INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialFee($baseInitialFee)
    {
        return $this->setData(self::BASE_INITIAL_FEE, $baseInitialFee);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialSubtotal()
    {
        return $this->getData(self::INITIAL_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialSubtotal($initialSubtotal)
    {
        return $this->setData(self::INITIAL_SUBTOTAL, $initialSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialSubtotal()
    {
        return $this->getData(self::BASE_INITIAL_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialSubtotal($baseInitialSubtotal)
    {
        return $this->setData(self::BASE_INITIAL_SUBTOTAL, $baseInitialSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialSubtotalInclTax()
    {
        return $this->getData(self::INITIAL_SUBTOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialSubtotalInclTax($initialSubtotalInclTax)
    {
        return $this->setData(self::INITIAL_SUBTOTAL_INCL_TAX, $initialSubtotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialSubtotalInclTax()
    {
        return $this->getData(self::BASE_INITIAL_SUBTOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialSubtotalInclTax($baseInitialSubtotalInclTax)
    {
        return $this->setData(self::BASE_INITIAL_SUBTOTAL_INCL_TAX, $baseInitialSubtotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialTaxAmount()
    {
        return $this->getData(self::INITIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialTaxAmount($initialTaxAmount)
    {
        return $this->setData(self::INITIAL_TAX_AMOUNT, $initialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialTaxAmount()
    {
        return $this->getData(self::BASE_INITIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialTaxAmount($baseInitialTaxAmount)
    {
        return $this->setData(self::BASE_INITIAL_TAX_AMOUNT, $baseInitialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialShippingAmount()
    {
        return $this->getData(self::INITIAL_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialShippingAmount($initialShippingAmount)
    {
        return $this->setData(self::INITIAL_SHIPPING_AMOUNT, $initialShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialShippingAmount()
    {
        return $this->getData(self::BASE_INITIAL_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialShippingAmount($baseInitialShippingAmount)
    {
        return $this->setData(self::BASE_INITIAL_SHIPPING_AMOUNT, $baseInitialShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialShippingAmountInclTax()
    {
        return $this->getData(self::INITIAL_SHIPPING_AMOUNT_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialShippingAmountInclTax($initialShippingAmountInclTax)
    {
        return $this->setData(self::INITIAL_SHIPPING_AMOUNT_INCL_TAX, $initialShippingAmountInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialShippingAmountInclTax()
    {
        return $this->getData(self::BASE_INITIAL_SHIPPING_AMOUNT_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialShippingAmountInclTax($baseInitialShippingAmountInclTax)
    {
        return $this->setData(self::BASE_INITIAL_SHIPPING_AMOUNT_INCL_TAX, $baseInitialShippingAmountInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialShippingTaxAmount()
    {
        return $this->getData(self::INITIAL_SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialShippingTaxAmount($initialShippingTaxAmount)
    {
        return $this->setData(self::INITIAL_SHIPPING_TAX_AMOUNT, $initialShippingTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialShippingTaxAmount()
    {
        return $this->getData(self::BASE_SHIPPING_INITIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialShippingTaxAmount($baseInitialShippingTaxAmount)
    {
        return $this->setData(self::BASE_SHIPPING_INITIAL_TAX_AMOUNT, $baseInitialShippingTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialGrandTotal()
    {
        return $this->getData(self::INITIAL_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialGrandTotal($initialGrandTotal)
    {
        return $this->setData(self::INITIAL_GRAND_TOTAL, $initialGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialGrandTotal()
    {
        return $this->getData(self::BASE_INITIAL_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialGrandTotal($baseInitialGrandTotal)
    {
        return $this->setData(self::BASE_INITIAL_GRAND_TOTAL, $baseInitialGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialSubtotal()
    {
        return $this->getData(self::TRIAL_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialSubtotal($trialSubtotal)
    {
        return $this->setData(self::TRIAL_SUBTOTAL, $trialSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialSubtotal()
    {
        return $this->getData(self::BASE_TRIAL_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialSubtotal($baseTrialSubtotal)
    {
        return $this->setData(self::BASE_TRIAL_SUBTOTAL, $baseTrialSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialSubtotalInclTax()
    {
        return $this->getData(self::TRIAL_SUBTOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialSubtotalInclTax($trialSubtotalInclTax)
    {
        return $this->setData(self::TRIAL_SUBTOTAL_INCL_TAX, $trialSubtotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialSubtotalInclTax()
    {
        return $this->getData(self::BASE_TRIAL_SUBTOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialSubtotalInclTax($baseTrialSubtotalInclTax)
    {
        return $this->setData(self::BASE_TRIAL_SUBTOTAL_INCL_TAX, $baseTrialSubtotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTaxAmount()
    {
        return $this->getData(self::TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTaxAmount($trialTaxAmount)
    {
        return $this->setData(self::TRIAL_TAX_AMOUNT, $trialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialTaxAmount()
    {
        return $this->getData(self::BASE_TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialTaxAmount($baseTrialTaxAmount)
    {
        return $this->setData(self::BASE_TRIAL_TAX_AMOUNT, $baseTrialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialShippingAmount()
    {
        return $this->getData(self::TRIAL_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialShippingAmount($trialShippingAmount)
    {
        return $this->setData(self::TRIAL_SHIPPING_AMOUNT, $trialShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialShippingAmount()
    {
        return $this->getData(self::BASE_TRIAL_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialShippingAmount($baseTrialShippingAmount)
    {
        return $this->setData(self::BASE_TRIAL_SHIPPING_AMOUNT, $baseTrialShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialShippingAmountInclTax()
    {
        return $this->getData(self::TRIAL_SHIPPING_AMOUNT_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialShippingAmountInclTax($trialShippingAmountInclTax)
    {
        return $this->setData(self::TRIAL_SHIPPING_AMOUNT_INCL_TAX, $trialShippingAmountInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialShippingAmountInclTax()
    {
        return $this->getData(self::BASE_TRIAL_SHIPPING_AMOUNT_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialShippingAmountInclTax($baseTrialShippingAmountInclTax)
    {
        return $this->setData(self::BASE_TRIAL_SHIPPING_AMOUNT_INCL_TAX, $baseTrialShippingAmountInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialShippingTaxAmount()
    {
        return $this->getData(self::TRIAL_SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialShippingTaxAmount($trialShippingTaxAmount)
    {
        return $this->setData(self::TRIAL_SHIPPING_TAX_AMOUNT, $trialShippingTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialShippingTaxAmount()
    {
        return $this->getData(self::BASE_SHIPPING_TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialShippingTaxAmount($baseTrialShippingTaxAmount)
    {
        return $this->setData(self::BASE_SHIPPING_TRIAL_TAX_AMOUNT, $baseTrialShippingTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialGrandTotal()
    {
        return $this->getData(self::TRIAL_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialGrandTotal($trialGrandTotal)
    {
        return $this->setData(self::TRIAL_GRAND_TOTAL, $trialGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialGrandTotal()
    {
        return $this->getData(self::BASE_TRIAL_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialGrandTotal($baseTrialGrandTotal)
    {
        return $this->setData(self::BASE_TRIAL_GRAND_TOTAL, $baseTrialGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularSubtotal()
    {
        return $this->getData(self::REGULAR_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularSubtotal($regularSubtotal)
    {
        return $this->setData(self::REGULAR_SUBTOTAL, $regularSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularSubtotal()
    {
        return $this->getData(self::BASE_REGULAR_SUBTOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularSubtotal($baseRegularSubtotal)
    {
        return $this->setData(self::BASE_REGULAR_SUBTOTAL, $baseRegularSubtotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularSubtotalInclTax()
    {
        return $this->getData(self::REGULAR_SUBTOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularSubtotalInclTax($regularSubtotalInclTax)
    {
        return $this->setData(self::REGULAR_SUBTOTAL_INCL_TAX, $regularSubtotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularSubtotalInclTax()
    {
        return $this->getData(self::BASE_REGULAR_SUBTOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularSubtotalInclTax($baseRegularSubtotalInclTax)
    {
        return $this->setData(self::BASE_REGULAR_SUBTOTAL_INCL_TAX, $baseRegularSubtotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularTaxAmount()
    {
        return $this->getData(self::REGULAR_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularTaxAmount($regularTaxAmount)
    {
        return $this->setData(self::REGULAR_TAX_AMOUNT, $regularTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularTaxAmount()
    {
        return $this->getData(self::BASE_REGULAR_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularTaxAmount($baseRegularTaxAmount)
    {
        return $this->setData(self::BASE_REGULAR_TAX_AMOUNT, $baseRegularTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularShippingAmount()
    {
        return $this->getData(self::REGULAR_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularShippingAmount($regularShippingAmount)
    {
        return $this->setData(self::REGULAR_SHIPPING_AMOUNT, $regularShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularShippingAmount()
    {
        return $this->getData(self::BASE_REGULAR_SHIPPING_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularShippingAmount($baseRegularShippingAmount)
    {
        return $this->setData(self::BASE_REGULAR_SHIPPING_AMOUNT, $baseRegularShippingAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularShippingAmountInclTax()
    {
        return $this->getData(self::REGULAR_SHIPPING_AMOUNT_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularShippingAmountInclTax($regularShippingAmountInclTax)
    {
        return $this->setData(self::REGULAR_SHIPPING_AMOUNT_INCL_TAX, $regularShippingAmountInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularShippingAmountInclTax()
    {
        return $this->getData(self::BASE_REGULAR_SHIPPING_AMOUNT_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularShippingAmountInclTax($baseRegularShippingAmountInclTax)
    {
        return $this->setData(self::BASE_REGULAR_SHIPPING_AMOUNT_INCL_TAX, $baseRegularShippingAmountInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularShippingTaxAmount()
    {
        return $this->getData(self::REGULAR_SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularShippingTaxAmount($regularShippingTaxAmount)
    {
        return $this->setData(self::REGULAR_SHIPPING_TAX_AMOUNT, $regularShippingTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularShippingTaxAmount()
    {
        return $this->getData(self::BASE_REGULAR_SHIPPING_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularShippingTaxAmount($baseRegularShippingTaxAmount)
    {
        return $this->setData(self::BASE_REGULAR_SHIPPING_TAX_AMOUNT, $baseRegularShippingTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularGrandTotal()
    {
        return $this->getData(self::REGULAR_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularGrandTotal($regularGrandTotal)
    {
        return $this->setData(self::REGULAR_GRAND_TOTAL, $regularGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularGrandTotal()
    {
        return $this->getData(self::BASE_REGULAR_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularGrandTotal($baseRegularGrandTotal)
    {
        return $this->setData(self::BASE_REGULAR_GRAND_TOTAL, $baseRegularGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentTokenId()
    {
        return $this->getData(self::PAYMENT_TOKEN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentTokenId($paymentTokenId)
    {
        return $this->setData(self::PAYMENT_TOKEN_ID, $paymentTokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastOrderId()
    {
        return $this->getData(self::LAST_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastOrderId($lastOrderId)
    {
        return $this->setData(self::LAST_ORDER_ID, $lastOrderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastOrderDate()
    {
        return $this->getData(self::LAST_ORDER_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastOrderDate($lastOrderDate)
    {
        return $this->setData(self::LAST_ORDER_DATE, $lastOrderDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getMembershipActiveUntilDate()
    {
        return $this->getData(self::MEMBERSHIP_ACTIVE_UNTIL_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMembershipActiveUntilDate($membershipActiveUntil)
    {
        return $this->setData(self::MEMBERSHIP_ACTIVE_UNTIL_DATE, $membershipActiveUntil);
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoteIp()
    {
        return $this->getData(self::REMOTE_IP);
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoteIp($remoteIp)
    {
        return $this->setData(self::REMOTE_IP, $remoteIp);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrePaymentInfo()
    {
        return $this->getData(self::PRE_PAYMENT_INFO);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrePaymentInfo($prePaymentInfo)
    {
        return $this->setData(self::PRE_PAYMENT_INFO, $prePaymentInfo);
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
    public function setExtensionAttributes(ProfileExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        if (!$this->validator) {
            $this->validator = $this->validatorFactory->create();
        }

        return $this->validator;
    }
}
