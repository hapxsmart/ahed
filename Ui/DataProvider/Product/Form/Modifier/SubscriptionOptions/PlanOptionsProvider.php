<?php
namespace Aheadworks\Sarp2\Ui\DataProvider\Product\Form\Modifier\SubscriptionOptions;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\ScopedPlan;
use Aheadworks\Sarp2\Model\Product\Checker\IsChildOfConfigurable as IsChildOfConfigurableChecker;
use Aheadworks\Sarp2\Model\Product\Type\Configurable\ParentProductResolver;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class PlanOptionsProvider
 *
 * @package Aheadworks\Sarp2\Ui\DataProvider\Product\Form\Modifier\SubscriptionOptions
 */
class PlanOptionsProvider
{
    /**
     * @var ScopedPlan
     */
    private $planSource;

    /**
     * @var IsChildOfConfigurableChecker
     */
    private $isChildOfConfigurableChecker;

    /**
     * @var ParentProductResolver
     */
    private $configurableParentProductResolver;

    /**
     * PlanOptionsProvider constructor.
     *
     * @param ScopedPlan $planSource
     * @param IsChildOfConfigurableChecker $isChildOfConfigurableChecker
     * @param ParentProductResolver $configurableParentProductResolver
     */
    public function __construct(
        ScopedPlan $planSource,
        IsChildOfConfigurableChecker $isChildOfConfigurableChecker,
        ParentProductResolver $configurableParentProductResolver
    ) {
        $this->planSource = $planSource;
        $this->isChildOfConfigurableChecker = $isChildOfConfigurableChecker;
        $this->configurableParentProductResolver = $configurableParentProductResolver;
    }

    /**
     * Retrieve plan options for ui select component
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getOptions($product)
    {
        $options = $this->planSource->toOptionArray();
        $isChildOfConfigurable = $this->isChildOfConfigurableChecker->check($product);

        if ($isChildOfConfigurable) {
            $parentProductSubscriptionOptions = $this->configurableParentProductResolver
                ->resolveParentProductSubscriptionOptions($product->getId());
            if (!empty($parentProductSubscriptionOptions)) {
                $parentPlanIds = $this->getProductPlanIds($parentProductSubscriptionOptions);
                foreach ($options as $index => $option) {
                    $planId = $option['value'];
                    if (!in_array($planId, $parentPlanIds)) {
                        unset($options[$index]);
                    }
                }
                $options = array_values($options);
            }
        }

        return $options;
    }

    /**
     * Get product unique subscription plan ids
     *
     * @param array $subscriptionOptions
     * @return int[]
     */
    private function getProductPlanIds($subscriptionOptions)
    {
        return array_reduce(
            $subscriptionOptions,
            function ($carry, $item) {
                $planId = $item[SubscriptionOptionInterface::PLAN_ID] ?? 0;
                if (!in_array($planId, $carry)) {
                    $carry[] = $planId;
                }

                return $carry;
            },
            []
        );
    }
}
