<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criterion;

use Aheadworks\Sarp2\Model\Quote\Item\Grouping\CriterionInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Field
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criterion
 */
class Field implements CriterionInterface
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $resultName;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var array
     */
    private $options = ['convertToBoolean' => true];

    /**
     * @param string $fieldName
     * @param string $resultName
     * @param BooleanUtils $booleanUtils
     * @param array $options
     */
    public function __construct(
        $fieldName,
        $resultName,
        BooleanUtils $booleanUtils,
        array $options = []
    ) {
        $this->fieldName = $fieldName;
        $this->resultName = $resultName;
        $this->booleanUtils = $booleanUtils;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($quoteItem)
    {
        return $quoteItem->getDataUsingMethod($this->fieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function getResultName()
    {
        return $this->resultName;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultValue($quoteItem)
    {
        $value = $quoteItem->getDataUsingMethod($this->fieldName);
        if (isset($this->options['convertToBoolean'])
            && $this->options['convertToBoolean']
        ) {
            $value = $this->booleanUtils->toBoolean($value);
        }
        return $value;
    }
}
