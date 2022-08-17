<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger;

use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Rule;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class EntityMerger
 * @package Aheadworks\Sarp2\Engine\Profile\Merger
 */
class EntityMerger
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        Factory $dataObjectFactory
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Merge source objects to destination
     *
     * @param object $destination
     * @param object[] $sources
     * @param Rule[] $rules
     * @param string $entityType
     * @return object
     */
    public function merge($destination, array $sources, array $rules, $entityType)
    {
        $data = $this->toDataObject($destination, $entityType);
        /** @var DataObject[] $sourceData */
        $sourceData = [];
        foreach ($sources as $source) {
            $sourceData[] = $this->toDataObject($source, $entityType);
        }

        foreach ($rules as $rule) {
            $fieldName = $rule->getFieldName();
            $mergeType = $rule->getSpecification()->getType();

            $firstValue = $sourceData[0]->getDataUsingMethod($fieldName);

            if ($mergeType == Specification::TYPE_SAME) {
                $data->setDataUsingMethod($fieldName, $firstValue);
            } elseif ($mergeType == Specification::TYPE_RESOLVABLE && $rule->getResolver()) {
                $data->setDataUsingMethod(
                    $fieldName,
                    $rule->getResolver()->getResolvedValue($sources, $fieldName)
                );
            } else {
                $isSame = true;
                if (count($sourceData) > 1) {
                    for ($index = 1; $index < count($sourceData); $index++) {
                        if ($sourceData[$index]->getDataUsingMethod($fieldName) != $firstValue) {
                            $isSame = false;
                        }
                    }
                }
                if ($isSame) {
                    $data->setDataUsingMethod($fieldName, $firstValue);
                } elseif ($rule->getResolver()) {
                    $data->setDataUsingMethod(
                        $fieldName,
                        $rule->getResolver()->getResolvedValue($sources, $fieldName)
                    );
                }
            }
        }

        $this->dataObjectHelper->populateWithArray($destination, $data->getData(), $entityType);
        return $destination;
    }

    /**
     * Convert entity instance to data object
     *
     * @param object $entity
     * @param string $entityType
     * @return DataObject
     */
    private function toDataObject($entity, $entityType)
    {
        $entityData = $this->dataObjectProcessor->buildOutputDataArray(
            $entity,
            $entityType
        );
        return $this->dataObjectFactory->create($entityData);
    }
}
