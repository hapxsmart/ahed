<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface PlanInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface PlanInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PLAN_ID = 'plan_id';
    const DEFINITION_ID = 'definition_id';
    const DEFINITION = 'definition';
    const STATUS = 'status';
    const NAME = 'name';
    const SORT_ORDER = 'sort_order';
    const REGULAR_PRICE_PATTERN_PERCENT = 'regular_price_pattern_percent';
    const TRIAL_PRICE_PATTERN_PERCENT = 'trial_price_pattern_percent';
    const PRICE_ROUNDING = 'price_rounding';
    const TITLES = 'titles';
    const STOREFRONT_TITLES = 'storefront_titles';
    /**#@-*/

    /**
     * Get plan ID
     *
     * @return int|null
     */
    public function getPlanId();

    /**
     * Set plan ID
     *
     * @param int $planId
     * @return $this
     */
    public function setPlanId($planId);

    /**
     * Get plan definition ID
     *
     * @return int|null
     */
    public function getDefinitionId();

    /**
     * Set plan definition ID
     *
     * @param int $definitionId
     * @return $this
     */
    public function setDefinitionId($definitionId);

    /**
     * Get plan definition
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface
     */
    public function getDefinition();

    /**
     * Set plan definition
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface $definition
     * @return $this
     */
    public function setDefinition($definition);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get sort order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int|null $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get regular price percentage of product price
     *
     * @return float
     */
    public function getRegularPricePatternPercent();

    /**
     * Set regular price percentage of product price
     *
     * @param float $percent
     * @return $this
     */
    public function setRegularPricePatternPercent($percent);

    /**
     * Get trial price percentage of product price
     *
     * @return float
     */
    public function getTrialPricePatternPercent();

    /**
     * Set regular price percentage of product price
     *
     * @param float $percent
     * @return $this
     */
    public function setTrialPricePatternPercent($percent);

    /**
     * Get price rounding type
     *
     * @return int
     */
    public function getPriceRounding();

    /**
     * Set price rounding type
     *
     * @param float $priceRounding
     * @return $this
     */
    public function setPriceRounding($priceRounding);

    /**
     * Get frontend titles
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanTitleInterface[]
     */
    public function getTitles();

    /**
     * Set frontend titles
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanTitleInterface[] $titles
     * @return $this
     */
    public function setTitles($titles);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\PlanExtensionInterface $extensionAttributes
    );
}
