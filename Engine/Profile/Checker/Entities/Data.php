<?php
namespace Aheadworks\Sarp2\Engine\Profile\Checker\Entities;

use Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Data
 */
class Data
{
    /**
     * @var RuleSet
     */
    private $ruleSet;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param RuleSet $ruleSet
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        RuleSet $ruleSet,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->ruleSet = $ruleSet;
    }

    /**
     * Check if entities merge able according to data merge specification
     *
     * @param object $firstEntity
     * @param object $secondEntity
     * @param string $entityType
     * @return bool
     */
    public function checkEntitiesData($firstEntity, $secondEntity, $entityType)
    {
        $data1 = $this->dataObjectProcessor->buildOutputDataArray($firstEntity, $entityType);
        $data2 = $this->dataObjectProcessor->buildOutputDataArray($secondEntity, $entityType);

        $fields = $this->ruleSet->getFields($entityType, Specification::TYPE_SAME);
        foreach ($fields as $field) {
            if (isset($data1[$field]) && isset($data2[$field])) {
                if ($data1[$field] != $data2[$field]) {
                    return false;
                }
            }
        }

        return true;
    }
}
