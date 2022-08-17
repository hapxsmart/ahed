<?php
namespace Aheadworks\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanExtensionInterface;
use Aheadworks\Sarp2\Model\Plan\Validator;
use Aheadworks\Sarp2\Model\ResourceModel\Plan as PlanResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Plan
 * @package Aheadworks\Sarp2\Model
 */
class Plan extends AbstractModel implements PlanInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Validator $validator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Validator $validator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(PlanResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanId()
    {
        return $this->getData(self::PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanId($planId)
    {
        return $this->setData(self::PLAN_ID, $planId);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitionId()
    {
        return $this->getData(self::DEFINITION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinitionId($definitionId)
    {
        return $this->setData(self::DEFINITION_ID, $definitionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return $this->getData(self::DEFINITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition($definition)
    {
        return $this->setData(self::DEFINITION, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPricePatternPercent()
    {
        return $this->getData(self::REGULAR_PRICE_PATTERN_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularPricePatternPercent($percent)
    {
        return $this->setData(self::REGULAR_PRICE_PATTERN_PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPricePatternPercent()
    {
        return $this->getData(self::TRIAL_PRICE_PATTERN_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPricePatternPercent($percent)
    {
        return $this->setData(self::TRIAL_PRICE_PATTERN_PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceRounding()
    {
        return $this->getData(self::PRICE_ROUNDING);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceRounding($priceRounding)
    {
        return $this->setData(self::PRICE_ROUNDING, $priceRounding);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitles()
    {
        return $this->getData(self::TITLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitles($titles)
    {
        return $this->setData(self::TITLES, $titles);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(PlanExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
