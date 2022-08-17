<?php
namespace Aheadworks\Sarp2\Model\Quote\Processor;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;

/**
 * Class Customer
 * @package Aheadworks\Sarp2\Model\Quote\Processor
 */
class Customer
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerManagement
     */
    private $customerManagement;

    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerManagement $customerManagement
     * @param HasSubscriptions $quoteChecker
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        CustomerManagement $customerManagement,
        HasSubscriptions $quoteChecker
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->customerManagement = $customerManagement;
        $this->quoteChecker = $quoteChecker;
    }

    /**
     * Process quote customer data
     *
     * @param Quote $quote
     * @return array
     */
    public function process(Quote $quote)
    {
        $addressesToSync = [];
        // todo: uncomment this when zero total quote (in the case of subscription only items) will be implemented
        /*if ($quote->getCustomerId() && $this->quoteChecker->checkHasSubscriptionsOnly($quote)) {
            $addressesToSync = $this->processAddresses($quote);
            $this->customerManagement->validateAddresses($quote);
        }*/
        $this->customerManagement->populateCustomerInfo($quote);
        return $addressesToSync;
    }

    /**
     * Process addresses
     *
     * @param Quote $quote
     * @return array
     */
    private function processAddresses($quote)
    {
        $addressesToSync = [];

        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $this->customerRepository->getById($quote->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();

        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            $shippingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddress);
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
            $addressesToSync[] = $shippingAddress->getId();
            $shipping->setCustomerAddressId($shippingAddress->getId());
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                if (!$hasDefaultShipping) {
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $billingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($billingAddress);
            $quote->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
            $addressesToSync[] = $billingAddress->getId();
            $billing->setCustomerAddressId($billingAddress->getId());
        }
        if ($shipping && !$shipping->getCustomerId() && !$hasDefaultBilling) {
            $shipping->setIsDefaultBilling(true);
        }

        return $addressesToSync;
    }
}
