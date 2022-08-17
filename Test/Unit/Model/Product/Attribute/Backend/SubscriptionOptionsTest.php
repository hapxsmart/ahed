<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Attribute\Backend;

use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\PostDataProcessor;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Validator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Validator\Exception as ValidatorException;
use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\Generator\Key as KeyGenerator;
use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\SaveAction;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions
 */
class SubscriptionOptionsTest extends \PHPUnit\Framework\TestCase
{
    const WEBSITE_ID = 1;
    const PRODUCT_ID = 2;

    /**
     * @var SubscriptionOptions
     */
    private $backend;

    /**
     * @var OptionResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var PostDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataProcessorMock;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactoryMock;

    /**
     * @var SaveAction|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saveActionMock;

    /**
     * @var KeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $keyGeneratorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->createMock(OptionResource::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->postDataProcessorMock = $this->createMock(PostDataProcessor::class);
        $this->validatorMock = $this->createMock(Validator::class);
        $this->dataObjectFactoryMock = $this->createMock(DataObjectFactory::class);
        $this->saveActionMock = $this->createMock(SaveAction::class);
        $this->keyGeneratorMock = $this->createMock(KeyGenerator::class);

        $this->backend = $objectManager->getObject(
            SubscriptionOptions::class,
            [
                'resource' => $this->resourceMock,
                'metadataPool' => $this->metadataPoolMock,
                'storeManager' => $this->storeManagerMock,
                'postDataProcessor' => $this->postDataProcessorMock,
                'validator' => $this->validatorMock,
                'dataObjectFactory' => $this->dataObjectFactoryMock,
                'saveActionMock' => $this->saveActionMock,
                'keyGeneratorMock' => $this->keyGeneratorMock
            ]
        );
    }

    /**
     * Test afterSave method if no options changed
     */
    public function testAfterSaveNotChanged()
    {
        $notChangedOption = $this->getOptionData(15, 1);

        $data = [$notChangedOption];
        $origData = [$notChangedOption];
        $productMock = $this->createProductMock($data, $origData, false);

        $this->postDataProcessorMock->expects($this->any())
            ->method('prepareEntityData')
            ->willReturnArgument(0);

        $this->backend->afterSave($productMock);
    }

    /**
     * Create product mock
     *
     * @param array $data
     * @param array $origData
     * @param bool $isChanged
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     * @throws \ReflectionException
     */
    private function createProductMock($data, $origData, $isChanged)
    {
        $storeId = 3;
        $attributeName = 'aw_sarp2_subscription_options';
        $linkField = 'entity_id';

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->createMock(Product::class);
        $attributeMock = $this->createMock(Attribute::class);
        $storeMock = $this->createMock(StoreInterface::class);
        $metadataMock = $this->createMock(EntityMetadataInterface::class);

        $this->setProperty('_attribute', $attributeMock);

        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $productMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $attributeMock->expects($this->once())
            ->method('getName')
            ->willReturn($attributeName);
        $productMock->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    [$attributeName, null, $data],
                    [$linkField, null, self::PRODUCT_ID]
                ]
            );
        $productMock->expects($this->any())
            ->method('getOrigData')
            ->with($attributeName)
            ->willReturn($origData);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn($linkField);
        if ($isChanged) {
            $productMock->expects($this->once())
                ->method('setData')
                ->with($attributeName . '_changed', 1);
        }

        return $productMock;
    }

    /**
     * Get option data
     *
     * @param int $optionId
     * @param int $planId
     * @param array $values
     * @return array
     */
    private function getOptionData($optionId, $planId, $values = [])
    {
        $sampleOption = [
            'option_id' => $optionId,
            'plan_id' => $planId,
            'product_id' => self::PRODUCT_ID,
            'website_id' => self::WEBSITE_ID,
            'initial_fee' => '10.0000',
            'trial_price' => '29.0000',
            'regular_price' => '29.0000',
            'is_auto_trial_price' => 1,
            'is_auto_regular_price' => 1
        ];

        if (is_array($values)) {
            $sampleOption = array_merge($sampleOption, $values);
        }

        return $sampleOption;
    }

    /**
     * Set property
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    private function setProperty($name, $value)
    {
        $class = new \ReflectionClass($this->backend);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->backend, $value);

        return $this;
    }
}
