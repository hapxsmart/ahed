<?php

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Plan $plan */
$plan = $objectManager->create(Plan::class);
$plan->load('Subscription Plan', PlanInterface::NAME);

try {
    /** @var PlanRepositoryInterface $planRepository */
    $planRepository = $objectManager->get(PlanRepositoryInterface::class);
    $planRepository->deleteById($plan->getPlanId());
} catch (NoSuchEntityException $exception) {
    // Plan already removed
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
