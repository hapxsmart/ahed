<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyPool;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PlanPriceCalculator;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class BundleOptionPriceModifier
 *
 * @package Aheadworks\Sarp2\Model\Sales\Order\Item\Option
 */
class BundleOptionPriceProcessor
{
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var PlanPriceCalculator
     */
    private $priceCalculator;

    /**
     * @param PlanInterfaceFactory $planFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Json $serializer
     * @param PlanPriceCalculator $priceCalculator
     */
    public function __construct(
        PlanInterfaceFactory $planFactory,
        DataObjectHelper $dataObjectHelper,
        Json $serializer,
        PlanPriceCalculator $priceCalculator
    ) {
        $this->planFactory = $planFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->serializer = $serializer;
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Process order item options
     *
     * @param array $options
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(array $options)
    {
        if ($this->isSubscription($options)) {
            if (isset($options['bundle_options'])) {
                $options = $this->processBundleOptions($options);
            }
            if (isset($options['bundle_selection_attributes'])) {
                $options = $this->processBundleSelectionAttributes($options);
            }
        }

        return $options;
    }

    /**
     * Check if an order item is a subscription
     *
     * @param array $options
     * @return bool
     */
    private function isSubscription($options)
    {
        if (isset($options['aw_sarp2_subscription_plan'])
            && is_array($options['aw_sarp2_subscription_plan'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Perform `bundle_options` field processing
     *
     * @param array $options
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processBundleOptions($options)
    {
        $plan = $this->getPlan($options);
        $paymentPeriod = $this->getPaymentPeriod($options);
        $bundleOptions = $options['bundle_options'];

        foreach ($bundleOptions as &$bundleOption) {
            foreach ($bundleOption['value'] as &$valueItem) {
                $valueItem['price'] = $this->priceCalculator->calculateAccordingPlan(
                    $valueItem['price'],
                    $plan,
                    $paymentPeriod,
                    PriceRounding::DONT_ROUND
                );
            }
        }

        $options['bundle_options'] = $bundleOptions;

        return $options;
    }

    /**
     * Perform `bundle_selection_attributes` field processing
     *
     * @param array $options
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processBundleSelectionAttributes($options)
    {
        $plan = $this->getPlan($options);
        $paymentPeriod = $this->getPaymentPeriod($options);
        $bundleSelectionAttributes = $this->serializer->unserialize($options['bundle_selection_attributes']);
        $bundleSelectionAttributes['price'] = $this->priceCalculator->calculateAccordingPlan(
            $bundleSelectionAttributes['price'],
            $plan,
            $paymentPeriod,
            PriceRounding::DONT_ROUND
        );;

        $options['bundle_selection_attributes'] = $this->serializer->serialize($bundleSelectionAttributes);

        return $options;
    }

    /**
     * Create plan object by plan data array
     *
     * @param array $options
     * @return PlanInterface
     */
    private function getPlan($options)
    {
        $planData = $options['aw_sarp2_subscription_plan'];
        /** @var PlanInterface $plan */
        $plan = $this->planFactory->create();
        $this->dataObjectHelper->populateWithArray($plan, $planData, PlanInterface::class);

        return $plan;
    }

    /**
     * Retrieve payment period
     *
     * @param $options
     * @return string|null
     */
    private function getPaymentPeriod($options)
    {
        return $options['aw_sarp2_subscription_payment_period'] ?? StrategyPool::TYPE_INITIAL;
    }
}
