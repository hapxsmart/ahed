<?php
namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Source\Status;

/**
 * Class Copier
 *
 * @package Aheadworks\Sarp2\Model\Plan
 */
class Copier
{
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @param PlanInterfaceFactory $planFactory
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        PlanInterfaceFactory $planFactory,
        PlanRepositoryInterface $planRepository
    ) {
        $this->planFactory = $planFactory;
        $this->planRepository = $planRepository;
    }

    /**
     * Create plan duplicate
     *
     * @param PlanInterface $plan
     * @return PlanInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function copy($plan)
    {
        /** @var PlanInterface $plan */
        $duplicate = $this->planFactory->create();
        $duplicate->setData($plan->getData());
        $duplicate->setStatus(Status::DISABLED);
        $duplicate->setName($duplicate->getName() . '-1');
        $duplicate->setPlanId(null);
        $duplicate->setDefinitionId(null);
        $duplicate->getDefinition()->setDefinitionId(null);
        foreach ($duplicate->getTitles() as $title) {
            $title->setPlanId(null);
            $title->setTitle($title->getTitle() . '-1');
        }

        return $this->planRepository->save($duplicate);
    }
}
