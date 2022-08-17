<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criterion;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping\CriterionInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Class PlanDefinition
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criterion
 */
class PlanDefinition implements CriterionInterface
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     */
    public function __construct(SubscriptionOptionRepositoryInterface $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($quoteItem)
    {
        $optionId = $quoteItem->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $subscriptionOptionId = $optionId->getValue();
            if ($subscriptionOptionId) {
                return $this->optionRepository->get($subscriptionOptionId)
                    ->getPlan()
                    ->getDefinitionId();
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultName()
    {
        return 'plan';
    }

    /**
     * {@inheritdoc}
     */
    public function getResultValue($quoteItem)
    {
        $optionId = $quoteItem->getOptionByCode('aw_sarp2_subscription_type');
        $subscriptionOptionId = $optionId->getValue();
        return $this->optionRepository->get($subscriptionOptionId)
            ->getPlan();
    }
}
