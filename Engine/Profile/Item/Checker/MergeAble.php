<?php
namespace Aheadworks\Sarp2\Engine\Profile\Item\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class MergeAble
 * @package Aheadworks\Sarp2\Engine\Profile\Item\Checker
 */
class MergeAble
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
        $this->ruleSet = $ruleSet;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Check if profile items can be merged
     *
     * @param ProfileItemInterface $item1
     * @param ProfileItemInterface $item2
     * @return bool
     */
    public function check(ProfileItemInterface $item1, ProfileItemInterface $item2)
    {
        $data1 = $this->dataObjectProcessor->buildOutputDataArray(
            $item1,
            ProfileItemInterface::class
        );
        $data2 = $this->dataObjectProcessor->buildOutputDataArray(
            $item2,
            ProfileItemInterface::class
        );

        $fields = $this->ruleSet->getFields(ProfileItemInterface::class, Specification::TYPE_SAME);
        foreach ($fields as $field) {
            if (isset($data1[$field]) && isset($data2[$field])) {
                if ($data1[$field] != $data2[$field]) {
                    return false;
                }
            }
        }

        $options1 = $item1->getProductOptions();
        $options2 = $item2->getProductOptions();

        $subscriptionType1 = $options1['info_buyRequest']['aw_sarp2_subscription_type'];
        $subscriptionType2 = $options2['info_buyRequest']['aw_sarp2_subscription_type'];
        if ($subscriptionType1 != $subscriptionType2) {
            return false;
        }

        return true;
    }
}
