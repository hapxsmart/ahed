<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Aheadworks\Sarp2\Model\Profile\Item\Finder as ProfileItemsFinder;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForProfile as ProfileDetailsViewModel;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForProfileItem as ProfileItemDetailsViewModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;

class SubscriptionOptionExtractor
{
    /**
     * @var ProfileItemDetailsViewModel
     */
    private $itemDetailsViewModel;

    /**
     * @var ProfileDetailsViewModel
     */
    private $profileDetailsViewModel;

    /**
     * @var ProfileFinder
     */
    private $profileFinder;

    /**
     * @var ProfileItemsFinder
     */
    private $profileItemsFinder;

    /**
     * @param ProfileDetailsViewModel $profileDetailsViewModel
     * @param ProfileItemDetailsViewModel $itemDetailsViewModel
     * @param ProfileFinder $profileFinder
     * @param ProfileItemsFinder $profileItemsFinder
     */
    public function __construct(
        ProfileDetailsViewModel $profileDetailsViewModel,
        ProfileItemDetailsViewModel $itemDetailsViewModel,
        ProfileFinder $profileFinder,
        ProfileItemsFinder $profileItemsFinder
    ) {
        $this->profileDetailsViewModel = $profileDetailsViewModel;
        $this->itemDetailsViewModel = $itemDetailsViewModel;
        $this->profileFinder = $profileFinder;
        $this->profileItemsFinder = $profileItemsFinder;
    }

    /**
     * Get options
     *
     * @param OrderItemInterface $orderItem
     * @param array $options
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function extract(OrderItemInterface $orderItem, array $options)
    {
        if (isset($options['aw_sarp2_subscription_plan'])
            && is_array($options['aw_sarp2_subscription_plan'])
        ) {
            $subscriptionOption = &$options['aw_sarp2_subscription_option'];
            $profile = $this->profileFinder->getByOrderAndPlan(
                $orderItem->getOrderId(),
                $options['aw_sarp2_subscription_plan']['plan_id']
            );
            if ($profile) {
                $profileItems = $this->profileItemsFinder->getItemsWithHiddenReplaced($profile);
                $profileItem = $this->getProfileItemByOrderItem($profileItems, $orderItem);
                $profileId = $profile->getProfileId();
                if ($profileItem) {
                    $subscriptionOption =  array_merge($subscriptionOption, [
                        'profile_id' => $profileId,
                        'initial_fee' => $profileItem->getInitialFee(),
                        'trial_price' => $profileItem->getTrialPrice(),
                        'regular_price' => $profileItem->getRegularPrice(),
                        'currency_code' => $profile->getProfileCurrencyCode(),
                        'created_date' => $this->profileDetailsViewModel->getCreatedDate($profile),
                        'trial_start_date' => $this->itemDetailsViewModel->getTrialStartDate($profileId),
                        'regular_start_date' => $this->itemDetailsViewModel->getRegularStartDate($profileId),
                        'regular_stop_date' => $this->profileDetailsViewModel->getRegularStopDate($profile)
                    ]);
                }
            }
        }
        return $subscriptionOption ?? [];
    }

    /**
     * Retrieve profile item by order item
     *
     * @param ProfileItemInterface[] $profileItems
     * @param OrderItemInterface $orderItem
     * @return ProfileItemInterface|null
     */
    private function getProfileItemByOrderItem( array $profileItems, OrderItemInterface $orderItem)
    {
        foreach ($profileItems as $profileItem) {
            if ($profileItem->getProductId() == $orderItem->getProductId()
                && $profileItem->getSku() == $orderItem->getSku()
            ) {
                return $profileItem;
            }
        }

        return null;
    }
}
