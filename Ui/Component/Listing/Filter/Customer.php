<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing\Filter;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Customer implements FilterApplierInterface
{
    /**
     * @var ShareConfig
     */
    private $shareConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ShareConfig $shareConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ShareConfig $shareConfig,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->shareConfig = $shareConfig;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function apply(Collection $collection, Filter $filter)
    {
        /** @var array $value */
        $value = $filter->getValue();
        $connection = $collection->getConnection();
        /** @var Select $select */
        $select = $collection->getSelect();

        $andCondition = [$this->prepareCustomerCondition($connection, $value)];
        if (!$this->shareConfig->isGlobalScope()) {
            $andCondition[] = $this->prepareStoresCondition($connection, $value);
        }

        $select->where(implode(' AND ', $andCondition));
    }

    /**
     * Prepare customer condition
     *
     * @param AdapterInterface $connection
     * @param array $customerData
     * @return string
     */
    private function prepareCustomerCondition($connection, $customerData)
    {
        return $connection->quoteInto(
                'main_table.customer_id = ?',
                $customerData['customer_id']
            );
    }

    /**
     * Prepare stores condition
     *
     * @param AdapterInterface $connection
     * @param array $customerData
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function prepareStoresCondition($connection, $customerData)
    {
        $customer = $this->customerRepository->getById($customerData['customer_id']);
        /** @var Website $customerWebsite */
        $customerWebsite = $this->storeManager->getWebsite($customer->getWebsiteId());
        return $connection->quoteInto(
            'main_table.store_id IN (?)',
            $customerWebsite->getStoreIds()
        );
    }
}
