<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\SaveAction;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Model\Config as EavConfig;
use Aheadworks\Sarp2\Model\ResourceModel\Product\EntityIdResolver;

/**
 * Class MassUpdateSubscriptionOptionsObserver
 * @package Aheadworks\Sarp2\Observer
 */
class MassUpdateSubscriptionOptionsObserver implements ObserverInterface
{
    /**
     * Subscription options attribute code
     */
    const SUBSCRIPTION_OPTIONS_ATTR_CODE = 'aw_sarp2_subscription_options';

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var SaveAction
     */
    private $saveAction;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EntityIdResolver
     */
    private $entityIdResolver;

    /**
     * @param EavConfig $eavConfig
     * @param SaveAction $saveAction
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductRepository $productRepository
     * @param EntityIdResolver $entityIdResolver
     */
    public function __construct(
        EavConfig $eavConfig,
        SaveAction $saveAction,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductRepository $productRepository,
        EntityIdResolver $entityIdResolver
    ) {
        $this->eavConfig = $eavConfig;
        $this->saveAction = $saveAction;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->entityIdResolver = $entityIdResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $attrData = $event->getData('attributes_data');
        $productIds = $event->getData('product_ids');

        $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');
        /** @var Product[] $products */
        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        foreach ($attrData as $attrCode => $values) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attrCode);
            if (!$attribute->getAttributeId()
                || $attribute->getAttributeCode() != self::SUBSCRIPTION_OPTIONS_ATTR_CODE
            ) {
                continue;
            }

            $newValues = [];
            foreach ($values as $value) {
                if (is_array($value)) {
                    $value['is_auto_regular_price'] = 1;
                    $value['is_auto_trial_price'] = 1;
                    $newValues[] = $value;
                }
            }

            if ($newValues) {
                foreach ($products as $product) {
                    $product->setStoreId(0);
                    $this->getAttribute($product, self::SUBSCRIPTION_OPTIONS_ATTR_CODE);
                    $this->saveAction->execute(
                        $this->entityIdResolver->resolve($product->getId()),
                        $newValues,
                        $product->getData(self::SUBSCRIPTION_OPTIONS_ATTR_CODE)
                    );
                }
            }
        }
        unset($attrData[self::SUBSCRIPTION_OPTIONS_ATTR_CODE]);
        $event->setData('attributes_data', $attrData);
    }

    /**
     * Retrieve product attribute by code
     *
     * @param Product $product
     * @param string $code
     * @return mixed
     */
    private function getAttribute(Product $product, $code)
    {
        if (!$product->hasData($code)) {
            $product->getResource()->load($product, $product->getId());
        }
        return $product->getData($code);
    }
}
