<?php
namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Helper\Validator\EmptyValidator;
use Aheadworks\Sarp2\Model\Plan\Source\StartDateType;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Plan
 */
class Validator extends AbstractValidator
{
    /**
     * @var EmptyValidator
     */
    private $emptyValidator;

    /**
     * @param EmptyValidator $emptyValidator
     */
    public function __construct(EmptyValidator $emptyValidator)
    {
        $this->emptyValidator = $emptyValidator;
    }

    /**
     * Returns true if and only if plan entity meets the validation requirements
     *
     * @param PlanInterface $plan
     * @return bool
     */
    public function isValid($plan)
    {
        $this->_clearMessages();

        if ($this->emptyValidator->isValid($plan->getName())) {
            $this->_addMessages(['Name is required.']);
        }
        if (!$this->isRange($plan->getRegularPricePatternPercent(), 0.0001, 100)) {
            $this->_addMessages(
                ['Regular payment price percentage must be greater than 0.0001 and less or equal to 100']
            );
        }
        if ($plan->getDefinition()->getIsTrialPeriodEnabled()
            && !$this->isPercentageRange($plan->getTrialPricePatternPercent())
        ) {
            $this->_addMessages(
                ['Trial payment price percentage must be greater or equal to 0 and less or equal to 100.']
            );
        }
        if ($this->emptyValidator->isValid($plan->getPriceRounding())) {
            $this->_addMessages(['Price rounding is required.']);
        }
        if (!$this->isDefinitionDataValid($plan)) {
            return false;
        }
        if (!$this->isTitlesDataValid($plan)) {
            return false;
        }

        return empty($this->getMessages());
    }

    /**
     * Returns true if and only if plan definition data is correct
     *
     * @param PlanInterface $plan
     * @return bool
     */
    private function isDefinitionDataValid(PlanInterface $plan)
    {
        $definition = $plan->getDefinition();

        if ($definition->getTotalBillingCycles()
            && !$this->isNumeric($definition->getTotalBillingCycles())
        ) {
            $this->_addMessages(['Number of payments is not a number.']);
            return false;
        }
        if ($definition->getStartDateType() == StartDateType::EXACT_DAY_OF_MONTH) {
            if ($this->emptyValidator->isValid($definition->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month is required.']);
                return false;
            } elseif (!$this->isNumeric($definition->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month is not a number.']);
                return false;
            } elseif (!$this->isGreaterThanZero($definition->getStartDateDayOfMonth())) {
                $this->_addMessages(['Day of month must be greater than 0.']);
                return false;
            }
        }
        if ($definition->getIsTrialPeriodEnabled()) {
            if ($this->emptyValidator->isValid($definition->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments is required.']);
                return false;
            } elseif (!$this->isNumeric($definition->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments is not a number.']);
                return false;
            } elseif (!$this->isGreaterThanZero($definition->getTrialTotalBillingCycles())) {
                $this->_addMessages(['Number of trial payments must be greater than 0.']);
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if and only if plan titles data are correct
     *
     * @param PlanInterface $plan
     * @return bool
     */
    private function isTitlesDataValid(PlanInterface $plan)
    {
        $isAllStoreViewsDataPresents = false;
        $titleStoreIds = [];
        if ($plan->getTitles()) {
            foreach ($plan->getTitles() as $title) {
                if (!in_array($title->getStoreId(), $titleStoreIds)) {
                    array_push($titleStoreIds, $title->getStoreId());
                } else {
                    $this->_addMessages(['Duplicated store view in storefront descriptions found.']);
                    return false;
                }
                if ($title->getStoreId() == 0) {
                    $isAllStoreViewsDataPresents = true;
                }

                if ($this->emptyValidator->isValid($title->getTitle())) {
                    $this->_addMessages(['Storefront title is required.']);
                    return false;
                }
            }
        }
        if (!$isAllStoreViewsDataPresents) {
            $this->_addMessages(
                ['Default values of storefront descriptions (for All Store Views option) aren\'t set.']
            );
            return false;
        }
        return true;
    }

    /**
     * Check if value is numeric
     *
     * @param int $value
     * @return bool
     */
    private function isNumeric($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return false;
        }

        return (bool)preg_match('/^\s*-?\d*(\.\d*)?\s*$/', (string)$value);
    }

    /**
     * Check if value greater than 0
     *
     * @param int $value
     * @return bool
     */
    private function isGreaterThanZero($value)
    {
        return $value > 0;
    }

    /**
     * Check if value is in the correct percentage range
     *
     * @param float $value
     * @param bool $inclusiveLeft
     * @return bool
     */
    private function isPercentageRange($value, $inclusiveLeft = true)
    {
        if (is_string($value)) {
            $value = (float)str_replace(',', '.', $value);
        }

        return $this->isRange($value, 0, 100, $inclusiveLeft, true);
    }

    /**
     * Check if value is in the correct range
     *
     * @param $value
     * @param $min
     * @param $max
     * @param bool $inclusiveMin
     * @param bool $inclusiveMax
     * @return bool
     */
    private function isRange($value, $min, $max, $inclusiveMin = true, $inclusiveMax = true)
    {
        $value = $this->stringToFloat($value);

        $greater = $inclusiveMin
            ? $value >= $min
            : $value > $min;
        $less = $inclusiveMax
            ? $value <= $max
            : $value < $max;

        return $greater && $less;
    }

    /**
     * Convert string to float
     *
     * @param string|float $value
     * @return float
     */
    private function stringToFloat($value)
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        return (float)$value;
    }
}
