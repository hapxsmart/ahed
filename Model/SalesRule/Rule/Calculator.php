<?php
namespace Aheadworks\Sarp2\Model\SalesRule\Rule;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\SalesRule\RulesApplier;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Calculator
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RulesApplier
     */
    private $rulesApplier;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param RulesApplier $rulesApplier
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        RulesApplier $rulesApplier,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->rulesApplier = $rulesApplier;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Process
     *
     * @param ProfileItemInterface $item
     * @return $this
     */
    public function process($item)
    {
        $profile = $this->profileRepository->get($item->getProfileId());
        $address = $profile->getBillingAddress();
        $items = $profile->getItems();
        $address->setAllItems($items);
        $item->setAddress($address);
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $child->setDiscountAmount(0);
                $child->setBaseDiscountAmount(0);
                $child->setDiscountPercent(0);
            }
        }

        $itemPrice = $this->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $appliedRuleIds = $this->rulesApplier->applyRules(
            $item,
            $this->_getRules($profile),
            false,
            null
        );

        $this->setAppliedRuleIds($item, $appliedRuleIds);
        return $this;
    }

    /**
     * Return item price
     *
     * @param ProfileItemInterface $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $product = $item->getProduct();
        $qty = $item->getQty();
        $price = $product->getFinalPrice($item->getQty());
        $product
            ->setBaseCalculationPrice($price)
            ->setCalculationPrice($price)
            ->setOriginalPrice($price)
            ->setBaseOriginalPrice($price)
            ->setFinalPrice($price)
            ->setQty($qty)
            ->setPriceCalculation(false);
        $item->setProductForRecalculation($product);
        return $price;
    }

    /**
     * Get rules collection for current object state
     *
     * @param ProfileInterface $profile
     * @return Collection
     * @throws LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    protected function _getRules($profile)
    {
        $store = $this->storeManager->getStore($profile->getStoreId());
        $websiteId = $store->getWebsiteId();
        $customerGroupId = $profile->getCustomerGroupId();
        $key = $websiteId . '_' . $customerGroupId;

        if (!isset($this->rules[$key])) {
            $this->rules[$key] = $this->collectionFactory->create()
                ->setValidationFilter($websiteId, $customerGroupId)
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('simple_action', ['by_fixed', 'by_percent'])
                ->load();
        }

        return $this->rules[$key];
    }

    /**
     * Set Applied Rule Ids
     *
     * @param ProfileItemInterface $item
     * @param int[] $appliedRuleIds
     * @return $this
     */
    public function setAppliedRuleIds($item, array $appliedRuleIds)
    {
        $item->setAppliedRuleIds(join(',', $appliedRuleIds));

        return $this;
    }
}
