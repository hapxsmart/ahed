<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Backend;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\SaveAction;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\SubscriptionType;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\PostDataProcessor;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Validator;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\Generator\Key as KeyGenerator;

/**
 * Class SubscriptionOptions
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Backend
 */
class SubscriptionOptions extends AbstractBackend
{
    /**
     * @var OptionResource
     */
    private $resource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var SaveAction
     */
    private $saveAction;

    /**
     * @var KeyGenerator
     */
    private $keyGenerator;

    /**
     * @param OptionResource $resource
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param PostDataProcessor $postDataProcessor
     * @param Validator $validator
     * @param DataObjectFactory $dataObjectFactory
     * @param SaveAction $saveAction
     * @param KeyGenerator $keyGenerator
     */
    public function __construct(
        OptionResource $resource,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        PostDataProcessor $postDataProcessor,
        Validator $validator,
        DataObjectFactory $dataObjectFactory,
        SaveAction $saveAction,
        KeyGenerator $keyGenerator
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->postDataProcessor = $postDataProcessor;
        $this->validator = $validator;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->saveAction = $saveAction;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $optionRows = $this->prepareRowsData(
            $object->getData($attributeName),
            $this->getProductType($object)
        );

        $subscriptionType = $object->getData('aw_sarp2_subscription_type');
        if (!empty($optionRows)) {
            /** @var Product $object */
            if ($this->hasDuplicates($optionRows, $object->getWebsiteIds())) {
                throw new LocalizedException(__('Duplicate plans are not allowed.'));
            }

            foreach ($optionRows as $row) {
                if (!$this->validator->isValid($row)) {
                    $errors = $this->validator->getMessages();
                    $exception = new ValidatorException(__(implode(PHP_EOL, $errors)));
                    foreach ($errors as $errorMessage) {
                        $exception->addMessage(new Error($errorMessage));
                    }
                    throw $exception;
                }
            }
        } elseif (!empty($subscriptionType) && $subscriptionType != SubscriptionType::NO) {
            throw new LocalizedException(__('Subscription Options are required.'));
        }

        return true;
    }

    /**
     * Check if option rows data has duplicate plans
     *
     * @param array $rows
     * @param array $websiteIds
     * @return bool
     */
    private function hasDuplicates(array $rows, array $websiteIds)
    {
        $unique = [];
        $duplicates = [];

        /**
         * Collect duplication data
         *
         * @param array $row
         * @param array $update
         * @return void
         */
        $collect = function ($row, $update = []) use (&$unique, &$duplicates) {
            $key = $this->keyGenerator->generate(
                $row,
                [SubscriptionOptionInterface::PLAN_ID, SubscriptionOptionInterface::WEBSITE_ID],
                $update
            );
            if (!in_array($key, $unique)) {
                $unique[] = $key;
            } else {
                $duplicates[] = $key;
            }
        };
        foreach ($rows as $row) {
            if ($row[SubscriptionOptionInterface::WEBSITE_ID] == 0) {
                foreach ($websiteIds as $websiteId) {
                    $collect($row, [SubscriptionOptionInterface::WEBSITE_ID => $websiteId]);
                }
            }
            $collect($row);
        }

        return count($duplicates) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($object)
    {
        $attribute = $this->getAttribute();
        $attributeName = $attribute->getName();

        $optionRows = $this->prepareRowsData(
            $object->getData($attributeName),
            $this->getProductType($object)
        );
        $origOptionRows = $object->getOrigData($attributeName);
        $productId = $this->getProductId($object);

        $isChanged = $this->saveAction->execute($productId, $optionRows, $origOptionRows);

        if ($isChanged) {
            $valueChangedKey = $attributeName . '_changed';
            $object->setData($valueChangedKey, 1);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad($object)
    {
        $data = $this->resource->loadOptionsData(
            $this->getProductId($object),
            $this->getWebsiteId($object)
        );
        $attributeName = $this->getAttribute()->getName();

        $object->setData($attributeName, $data);
        $object->setOrigData($attributeName, $data);

        $valueChangedKey = $attributeName . '_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);

        return $this;
    }

    /**
     * Get product Id
     *
     * @param DataObject $object
     * @return int
     * @throws \Exception
     */
    private function getProductId($object)
    {
        return $object->getData(
            $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
        );
    }

    /**
     * Get product Type
     *
     * @param DataObject $object
     * @return string
     * @throws \Exception
     */
    private function getProductType($object)
    {
        return $object->getData(ProductInterface::TYPE_ID);
    }

    /**
     * Get website Id
     *
     * @param Product|DataObject $object
     * @return int
     */
    private function getWebsiteId($object)
    {
        return $this->storeManager->getStore($object->getStoreId())
            ->getWebsiteId();
    }

    /**
     * {@inheritdoc}
     */
    public function isScalar()
    {
        return false;
    }

    /**
     * Prepare option rows data
     *
     * @param array $rows
     * @param string $productType
     * @return array
     */
    private function prepareRowsData($rows, $productType)
    {
        $rows = array_filter((array)$rows);
        foreach ($rows as $index => $row) {
            $rows[$index] = $this->postDataProcessor->prepareEntityData($row, $productType);
        }
        return $rows;
    }
}
