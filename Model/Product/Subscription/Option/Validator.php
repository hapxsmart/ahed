<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Helper\Validator\EmptyValidator;
use Aheadworks\Sarp2\Model\Plan\Source\Status as PlanStatus;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Locale\FormatInterface;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class Validator extends AbstractValidator
{
    /**
     * @var SubscriptionOptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var EmptyValidator
     */
    private $emptyValidator;

    /**
     * @param SubscriptionOptionInterfaceFactory $optionFactory
     * @param PlanRepositoryInterface $planRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param FormatInterface $localeFormat
     * @param EmptyValidator $emptyValidator
     */
    public function __construct(
        SubscriptionOptionInterfaceFactory $optionFactory,
        PlanRepositoryInterface $planRepository,
        DataObjectHelper $dataObjectHelper,
        FormatInterface $localeFormat,
        EmptyValidator $emptyValidator
    ) {
        $this->planRepository = $planRepository;
        $this->optionFactory = $optionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->localeFormat = $localeFormat;
        $this->emptyValidator = $emptyValidator;
    }

    /**
     * Returns true if and only if subscription option entity meets the validation requirements
     *
     * @param SubscriptionOptionInterface|array $option
     * @return bool
     */
    public function isValid($option)
    {
        $this->_clearMessages();

        if (is_array($option)) {
            /** @var SubscriptionOptionInterface $optionEntity */
            $optionEntity = $this->optionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $optionEntity,
                $option,
                SubscriptionOptionInterface::class
            );
        } else {
            $optionEntity = $option;
        }

        $planId = $optionEntity->getPlanId();
        if ($this->emptyValidator->isValid($planId)) {
            $this->_addMessages(['Plan Id is required.']);
        } else {
            if ($this->emptyValidator->isValid($optionEntity->getWebsiteId())) {
                $this->_addMessages(['Website Id is required.']);
            }

            $plan = $this->planRepository->get($planId);
            if ($plan->getStatus() == PlanStatus::ENABLED) {
                $planDefinition = $plan->getDefinition();

                if ($planDefinition->getIsInitialFeeEnabled()) {
                    $initialFee = $optionEntity->getInitialFee();

                    if ($this->emptyValidator->isValid($initialFee)) {
                        $this->_addMessages(['Initial fee is required.']);
                    } elseif (!$this->isNumeric($initialFee)) {
                        $this->_addMessages(['Please enter a valid number for initial fee.']);
                    } elseif ($initialFee <= 0) {
                        $this->_addMessages(['Initial fee must be greater than 0.']);
                    }
                }
                if ($planDefinition->getIsTrialPeriodEnabled()
                    && !$optionEntity->getIsAutoTrialPrice()
                ) {
                    $trialPrice = $optionEntity->getTrialPrice();

                    if ($this->emptyValidator->isValid($trialPrice)) {
                        $this->_addMessages(['Trial price is required.']);
                    } elseif (!$this->isNumeric($trialPrice)) {
                        $this->_addMessages(['Please enter a valid number for trial price.']);
                    } elseif ($trialPrice < 0) {
                        $this->_addMessages(['Trial price must be equal or greater than 0.']);
                    }
                }
                if (!$optionEntity->getIsAutoRegularPrice()) {
                    $regularPrice = $optionEntity->getRegularPrice();

                    if ($this->emptyValidator->isValid($regularPrice)) {
                        $this->_addMessages(['Regular price is required.']);
                    } elseif (!$this->isNumeric($regularPrice)) {
                        $this->_addMessages(['Please enter a valid number for regular price.']);
                    } elseif ($regularPrice <= 0) {
                        $this->_addMessages(['Regular price must be greater than 0.']);
                    }
                }
            }
        }

        return empty($this->getMessages());
    }

    /**
     * Check if value is numeric according to locale format
     *
     * @param mixed $value
     * @return bool
     */
    private function isNumeric($value)
    {
        $value = $this->localeFormat->getNumber($value);

        return is_numeric($value);
    }
}
