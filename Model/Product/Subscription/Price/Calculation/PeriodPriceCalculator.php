<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyPool;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException as LocalizedExceptionAlias;

/**
 * Class PeriodPriceCalculator
 */
class PeriodPriceCalculator
{
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var PlanPriceCalculator
     */
    private $baseCalculator;

    /**
     * @var ProductPriceResolver
     */
    private $priceResolver;

    /**
     * @param PlanRepositoryInterface $planRepository
     * @param PlanPriceCalculator $amountCalculator
     * @param ProductPriceResolver $priceResolver
     */
    public function __construct(
        PlanRepositoryInterface $planRepository,
        PlanPriceCalculator $amountCalculator,
        ProductPriceResolver $priceResolver
    ) {
        $this->planRepository = $planRepository;
        $this->baseCalculator = $amountCalculator;
        $this->priceResolver = $priceResolver;
    }

    /**
     * @param Input $input
     * @param int $planId
     * @param null $forceUseAdvancedConfig
     * @return float
     * @throws LocalizedExceptionAlias
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateTrialPrice($input, $planId, $forceUseAdvancedConfig = null)
    {
        return $this->calculatePrice(
            $input,
            $planId,
            StrategyPool::TYPE_TRIAL,
            $forceUseAdvancedConfig
        );
    }

    /**
     * @param Input $input
     * @param int $planId
     * @param null $forceUseAdvancedConfig
     * @return float
     * @throws LocalizedExceptionAlias
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateRegularPrice($input, $planId, $forceUseAdvancedConfig = null)
    {
        return $this->calculatePrice(
            $input,
            $planId,
            StrategyPool::TYPE_REGULAR,
            $forceUseAdvancedConfig
        );
    }

    /**
     * Calculate subscription price for trial/regular subscription period
     *
     * @param Input $input
     * @param int $planId
     * @param string $dataResolverStartegyType
     * @param null $forceUseAdvancedConfig
     * @return float
     * @throws LocalizedExceptionAlias
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function calculatePrice($input, $planId, $dataResolverStartegyType, $forceUseAdvancedConfig = null)
    {
        $productPrice = $this->priceResolver->getPrice($input, $forceUseAdvancedConfig);
        $subscriptionPrice = $this->baseCalculator->calculateAccordingPlan(
            $productPrice,
            $planId,
            $dataResolverStartegyType
        );

        return $subscriptionPrice;
    }
}
