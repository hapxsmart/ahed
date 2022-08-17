<?php
namespace Aheadworks\Sarp2\CustomerData\Cart;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;

/**
 * Class ItemDataProcessor
 * @package Aheadworks\Sarp2\CustomerData\Cart
 */
class ItemDataProcessor
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        SubscriptionOptionRepositoryInterface $optionRepository
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Process cart item data
     *
     * @param Item $item
     * @param array $data
     * @return array
     */
    public function process(Item $item, array $data)
    {
        $isSubscription = $this->isSubscriptionChecker->check($item);
        $data['aw_sarp_is_subscription'] = $isSubscription;
        if ($isSubscription) {
            $option = $item->getOptionByCode('aw_sarp2_subscription_type');
            $parentOption = $item->getOptionByCode('aw_sarp2_parent_subscription_type');
            if ($parentOption) {
                $data['aw_sarp_subscription_type'] = $parentOption->getValue();
                $data['aw_sarp_frontend_displaying_mode'] = $this->getFrontendDisplayingMode($parentOption->getValue());
            } elseif ($option) {
                $data['aw_sarp_subscription_type'] = $option->getValue();
                $data['aw_sarp_frontend_displaying_mode'] = $this->getFrontendDisplayingMode($option->getValue());
            }
        }
        return $data;
    }

    /**
     * Get frontend displaying mode by option id
     *
     * @param int $optionId
     * @return string
     * @throws LocalizedException
     */
    private function getFrontendDisplayingMode($optionId) {
        $subscriptionOption = $this->optionRepository->get($optionId);
        $planDefinition = $subscriptionOption->getPlan()->getDefinition();

        return $planDefinition->getFrontendDisplayingMode();
    }
}
