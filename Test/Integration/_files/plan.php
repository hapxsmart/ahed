<?php

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;
use Aheadworks\Sarp2\Model\Plan\Source\StartDateType;
use Aheadworks\Sarp2\Model\Plan\Source\Status;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var PlanInterfaceFactory $planFactory */
$planFactory = $objectManager->get(PlanInterfaceFactory::class);
/** @var PlanDefinitionInterfaceFactory $planDefinitionFactory */
$planDefinitionFactory = $objectManager->get(PlanDefinitionInterfaceFactory::class);
/** @var PlanTitleInterfaceFactory $planTitleFactory */
$planTitleFactory = $objectManager->get(PlanTitleInterfaceFactory::class);
/** @var PlanRepositoryInterface $planRepository */
$planRepository = $objectManager->get(PlanRepositoryInterface::class);

/** @var PlanDefinitionInterface $planDefinition */
$planDefinition = $planDefinitionFactory->create();
$planDefinition->setBillingPeriod(BillingPeriod::DAY)
    ->setBillingFrequency(1)
    ->setIsInitialFeeEnabled(true)
    ->setIsTrialPeriodEnabled(true)
    ->setStartDateType(StartDateType::MOMENT_OF_PURCHASE)
    ->setTotalBillingCycles(0)
    ->setTrialTotalBillingCycles(5);

/** @var PlanTitleInterface $planTitle */
$planTitle = $planTitleFactory->create();
$planTitle->setStoreId(0)
    ->setTitle('Subscription Plan');

/** @var PlanInterface $plan */
$plan = $planFactory->create();
$plan->setName('Subscription Plan')
    ->setDefinition($planDefinition)
    ->setPriceRounding(PriceRounding::UP_TO_XX_99)
    ->setRegularPricePatternPercent(90)
    ->setTrialPricePatternPercent(80)
    ->setWebsiteId(1)
    ->setStatus(Status::ENABLED)
    ->setTitles([$planTitle]);
$planRepository->save($plan);
