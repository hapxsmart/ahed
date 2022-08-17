<?php
namespace Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver as PlanTitleResolver;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Aheadworks\Sarp2\Model\Sales\Order\Item\SubscriptionOptionExtractor;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForProfile as ProfileDetailsViewModel;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForProfileItem as ProfileItemDetailsViewModel;
use Aheadworks\Sarp2\Model\Profile\View\LinkRenderer as ProfileViewLinkRenderer;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\State as AppState;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProfileOptionProcessor implements ProcessorInterface
{
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanTitleResolver
     */
    private $planTitleResolver;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ProfileItemDetailsViewModel
     */
    private $itemDetailsViewModel;

    /**
     * @var ProfileDetailsViewModel
     */
    private $profileDetailsViewModel;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var ProfileViewLinkRenderer
     */
    private $profileViewLinkRenderer;

    /**
     * @var SubscriptionOptionExtractor
     */
    private $subscriptionOptionExtractor;

    /**
     * @param PlanInterfaceFactory $planFactory
     * @param PlanTitleResolver $planTitleResolver
     * @param DataObjectHelper $dataObjectHelper
     * @param ProfileItemDetailsViewModel $itemDetailsViewModel
     * @param ProfileDetailsViewModel $profileDetailsViewModel
     * @param AppState $appState
     * @param ProfileViewLinkRenderer $profileViewLinkRenderer
     * @param SubscriptionOptionExtractor $subscriptionOptionExtractor
     */
    public function __construct(
        PlanInterfaceFactory $planFactory,
        PlanTitleResolver $planTitleResolver,
        DataObjectHelper $dataObjectHelper,
        ProfileItemDetailsViewModel $itemDetailsViewModel,
        ProfileDetailsViewModel $profileDetailsViewModel,
        AppState $appState,
        ProfileViewLinkRenderer $profileViewLinkRenderer,
        SubscriptionOptionExtractor $subscriptionOptionExtractor
    ) {
        $this->planFactory = $planFactory;
        $this->planTitleResolver = $planTitleResolver;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->itemDetailsViewModel = $itemDetailsViewModel;
        $this->profileDetailsViewModel = $profileDetailsViewModel;
        $this->appState = $appState;
        $this->profileViewLinkRenderer = $profileViewLinkRenderer;
        $this->subscriptionOptionExtractor = $subscriptionOptionExtractor;
    }

    /**
     * Check if an order item is a subscription
     *
     * @param array $options
     * @return bool
     */
    public function isSubscription($options)
    {
        if (isset($options['aw_sarp2_subscription_plan'])
            && is_array($options['aw_sarp2_subscription_plan'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function process(OrderItem $orderItem, array $options)
    {
        $subscriptionOptions = [];
        if ($this->isSubscription($options)) {
            $plan = $this->getPlan($options);
            $planDefinition = $plan->getDefinition();
            $subscriptionOption = &$options['aw_sarp2_subscription_option'];
            $planTitle = $this->getPlanTitle($plan);
            if (!isset($subscriptionOption['profile_id'])) {
                $subscriptionOption = array_merge(
                    $subscriptionOption,
                    $this->subscriptionOptionExtractor->extract($orderItem, $options)
                );
            }
            if (isset($subscriptionOption['profile_id'])) {
                $addOption = function ($label, $value) use (&$subscriptionOptions, $plan) {
                    $subscriptionOptions[] = [
                        'label' => $label,
                        'value' => $value,
                        'aw_sarp2_subscription_plan' => $plan->getPlanId(),
                        // require for reorder from customer account
                        'option_id' => null,
                        'option_value' => null
                    ];
                };
                if ($this->isAdmin()) {
                    $subscriptionOptions[] = [
                        'label' => __('Subscription Profile ID'),
                        'value' => $this->profileViewLinkRenderer->renderSubscriptionViewLinkHtml(
                            $subscriptionOption['profile_id']
                        ),
                        'aw_sarp2_subscription_plan' => $plan->getPlanId(),
                        'custom_view' => true,
                        'option_id' => null,
                        'option_value' => null
                    ];
                }

                $addOption(
                    $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                        ? __('Payment Schedule')
                        : __('Payment Schedule'),
                    $planTitle
                );
                $addOption(
                    $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                        ? __('Installment Created On')
                        : __('Subscription Created On'),
                    $subscriptionOption['created_date']
                );

                if ($planDefinition->getIsInitialFeeEnabled()) {
                    $addOption(
                        $this->itemDetailsViewModel->getInitialLabel(),
                        $this->itemDetailsViewModel->getInitialPaymentPrice($options)
                    );
                }
                if ($this->itemDetailsViewModel->isShowTrialDetails($planDefinition)) {
                    $addOption(
                        $this->itemDetailsViewModel->getTrialLabel($planDefinition, $options),
                        __('%1 starting %2', [
                            $this->itemDetailsViewModel->getTrialPriceAndCycles($planDefinition, $options),
                            $subscriptionOption['trial_start_date']
                        ])
                    );
                }
                if ($this->itemDetailsViewModel->isShowRegularDetails($planDefinition)) {
                    $addOption(
                        $this->itemDetailsViewModel->getRegularLabel($planDefinition),
                        __('%1 starting %2', [
                            $this->itemDetailsViewModel->getRegularPriceAndCycles($planDefinition, $options),
                            $subscriptionOption['regular_start_date']
                        ])
                    );
                }

                $addOption(
                    $this->profileDetailsViewModel->getSubscriptionEndLabel($planDefinition),
                    $subscriptionOption['regular_stop_date']
                );
            }
        }

        if (isset($options['options'])) {
            $this->removeSubscriptionOptions($options['options']);
            $options['options'] = array_merge($options['options'], $subscriptionOptions);
        } else {
            $options['options'] = $subscriptionOptions;
        }

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
     * Retrieve plan title
     *
     * @param PlanInterface $plan
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getPlanTitle($plan)
    {
        return $this->isAdmin() ? $plan->getName() : $this->planTitleResolver->getTitle($plan);
    }

    /**
     * Check if admin app state
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isAdmin()
    {
        return $this->appState->getAreaCode() == 'adminhtml';
    }

    /**
     * Remove subscription options
     *
     * @param array $options
     * @return array
     */
    public function removeSubscriptionOptions(&$options)
    {
        foreach ($options as $index => $optionData) {
            if (isset($optionData['aw_sarp2_subscription_plan'])) {
                unset($options[$index]);
            }
        }

        return $options;
    }
}
