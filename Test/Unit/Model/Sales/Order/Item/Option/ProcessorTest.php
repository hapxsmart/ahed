<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Order\Item\Option;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Item\Finder as ProfileItemsFinder;
use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor\ProfileOptionProcessor;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver as PlanTitleResolver;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForProfileItem as ForProfileItem;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForProfile;
use Aheadworks\Sarp2\Model\Profile\View\LinkRenderer as ProfileViewLinkRenderer;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\State as AppState;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var ProfileOptionProcessor
     */
    private $processor;

    /**
     * @var PlanInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planFactoryMock;

    /**
     * @var PlanTitleResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planTitleResolverMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var ProfileFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileFinderMock;

    /**
     * @var ProfileItemsFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileItemsFinderMock;

    /**
     * @var ForProfileItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemDetailsViewModelMock;

    /**
     * @var ForProfile|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileDetailsViewModelMock;

    /**
     * @var AppState|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var ProfileViewLinkRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileViewLinkRendererMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->planFactoryMock = $this->createMock(PlanInterfaceFactory::class);
        $this->planTitleResolverMock = $this->createMock(PlanTitleResolver::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->profileFinderMock = $this->createMock(ProfileFinder::class);
        $this->profileItemsFinderMock = $this->createMock(ProfileItemsFinder::class);
        $this->itemDetailsViewModelMock = $this->createMock(ForProfileItem::class);
        $this->profileDetailsViewModelMock = $this->createMock(ForProfile::class);
        $this->appStateMock = $this->createMock(AppState::class);
        $this->profileViewLinkRendererMock = $this->createMock(ProfileViewLinkRenderer::class);

        $this->processor = $objectManager->getObject(
            ProfileOptionProcessor::class,
            [
                'planFactory' => $this->planFactoryMock,
                'planTitleResolver' => $this->planTitleResolverMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'profileFinder' => $this->profileFinderMock,
                'profileItemsFinder' => $this->profileItemsFinderMock,
                'itemDetailsViewModel' => $this->itemDetailsViewModelMock,
                'profileDetailsViewModel' => $this->profileDetailsViewModelMock,
                'appState' => $this->appStateMock,
                'profileViewLinkRenderer' => $this->profileViewLinkRendererMock
            ]
        );
    }

    /**
     * Test isSubscription method
     *
     * @param array $options
     * @param bool $result
     * @dataProvider isSubscriptionDataProvider
     */
    public function testIsSubscription($options, $result)
    {
        $this->assertEquals($result, $this->processor->isSubscription($options));
    }

    /**
     * @return array
     */
    public function isSubscriptionDataProvider()
    {
        return [
            [
                'options' => [],
                'result' => false,
            ],
            [
                'options' => [
                    'aw_sarp2_subscription_plan' => null
                ],
                'result' => false,
            ],
            [
                'options' => [
                    'aw_sarp2_subscription_plan' => []
                ],
                'result' => true,
            ],
        ];
    }

    /**
     * Test getDetailedSubscriptionOptions method
     *
     * @param array $input
     * @param string $planName
     * @param string $planTitle
     * @param OrderItem $orderItem
     * @param array $options
     * @param bool $isAdmin
     * @param bool $isTrialEnabled
     * @param array $result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \ReflectionException
     * @dataProvider getDetailedSubscriptionOptionsDataProvider
     */
    public function testGetDetailedSubscriptionOptions($input, $result)
    {
        $productId = 1;
        $productSku = 'sku';
        $options = $input['options'];
        $orderItemMock = $this->createMock(OrderItem::class);

        $orderItemMock->expects($this->any())
            ->method('getOrderId')
            ->willReturn($input['orderItemId']);
        $orderItemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $orderItemMock->expects($this->any())
            ->method('getSku')
            ->willReturn($productSku);

        if (isset($options['aw_sarp2_subscription_plan'])) {
            $planData = $options['aw_sarp2_subscription_plan'];
            $profileMock = $this->createMock(ProfileInterface::class);
            $profileItemMock = $this->createMock(ProfileItemInterface::class);
            $planDefinitionMock = $this->createMock(PlanDefinitionInterface::class);

            $profileItemMock->expects($this->any())
                ->method('getProductId')
                ->willReturn($productId);
            $profileItemMock->expects($this->any())
                ->method('getSku')
                ->willReturn($productSku);
            $this->appStateMock->expects($this->exactly(2))
                ->method('getAreaCode')
                ->willReturn($input['isAdmin']);
            $planMock = $this->createMock(PlanInterface::class);
            if (isset($planData['plan_id'])) {
                $planMock->expects($this->any())
                    ->method('getPlanId')
                    ->willReturn($planData['plan_id']);
            }
            $this->planFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($planMock);

            $this->dataObjectHelperMock->expects($this->once())
                ->method('populateWithArray')
                ->with($planMock, $planData, PlanInterface::class)
                ->willReturnSelf();
            $definitionMock = $this->getMockForAbstractClass(PlanDefinitionInterface::class);
            $planMock->expects($this->any())
                ->method('getDefinition')
                ->willReturn($definitionMock);

            $this->profileFinderMock->expects($this->any())
                ->method('getByOrderAndPlan')
                ->with($input['orderItemId'], $planData['plan_id'])
                ->willReturn($profileMock);
            $this->profileItemsFinderMock->expects($this->any())
                ->method('getItemsWithHiddenReplaced')
                ->with($profileMock)
                ->willReturn([$profileItemMock]);
            $profileId = 1;
            if ($input['isAdmin']) {
                $planMock->expects($this->once())
                    ->method('getName')
                    ->willReturn($input['planName']);

                $this->profileViewLinkRendererMock->expects($this->once())
                    ->method('renderSubscriptionViewLinkHtml')
                    ->with($profileId)
                    ->willReturn($input['subscriptionProfileLink']);

            } else {
                $this->planTitleResolverMock->expects($this->once())
                    ->method('getTitle')
                    ->with($planMock)
                    ->willReturn($input['planTitle']);
            }
            $profileMock->expects($this->once())
                ->method('getPlanDefinition')
                ->willReturn($planDefinitionMock);
            $isTrialEnabled = $input['isTrialEnabled'];
            $this->itemDetailsViewModelMock->expects($this->once())
                ->method('isShowTrialDetails')
                ->with($definitionMock)
                ->willReturn($isTrialEnabled);
            $profileMock->expects($this->any())
                    ->method('getProfileId')
                    ->willReturn($profileId);
            if ($isTrialEnabled) {
                $this->itemDetailsViewModelMock->expects($this->once())
                    ->method('getTrialLabel')
                    ->with($definitionMock, $options)
                    ->willReturn(__($input['trialLabel']));
                $this->itemDetailsViewModelMock->expects($this->once())
                    ->method('getTrialPriceAndCycles')
                    ->with($definitionMock, $options)
                    ->willReturn($input['trialPriceAndCycles']);
                $this->itemDetailsViewModelMock->expects($this->any())
                    ->method('getTrialStartDate')
                    ->with($profileId)
                    ->willReturn($input['trialStartDate']);
            }
            $this->itemDetailsViewModelMock->expects($this->any())
                ->method('isShowInitialDetails')
                ->with($profileItemMock)
                ->willReturn(false);
            $this->itemDetailsViewModelMock->expects($this->once())
                ->method('isShowRegularDetails')
                ->with($definitionMock)
                ->willReturn(true);
            $this->profileDetailsViewModelMock->expects($this->any())
                ->method('getCreatedDate')
                ->with($profileMock)
                ->willReturn(__($input['subscriptionStartDate']));
            $this->itemDetailsViewModelMock->expects($this->once())
                ->method('getRegularLabel')
                ->with($definitionMock)
                ->willReturn(__($input['regularLabel']));
            $this->itemDetailsViewModelMock->expects($this->once())
                ->method('getRegularPriceAndCycles')
                ->with($definitionMock, $options)
                ->willReturn($input['regularPriceAndCycles']);
            $this->itemDetailsViewModelMock->expects($this->any())
                ->method('getRegularStartDate')
                ->with($profileId)
                ->willReturn($input['regularStartDate']);
            $this->profileDetailsViewModelMock->expects($this->once())
                ->method('getSubscriptionEndLabel')
                ->with($planDefinitionMock)
                ->willReturn($input['stopLabel']);
            $this->profileDetailsViewModelMock->expects($this->any())
                ->method('getRegularStopDate')
                ->with($profileMock)
                ->willReturn($input['regularStopDate']);
        }

        $this->assertEquals(
            $result,
            $this->processor->process($orderItemMock, $options)
        );
    }

    /**
     * @return array
     */
    public function getDetailedSubscriptionOptionsDataProvider()
    {
        return [
            [
                'input' => [
                    'planName' => 'Plan Name',
                    'planTitle' => 'Store Plan Name',
                    'options' => [
                        'aw_sarp2_subscription_plan' => [
                            'plan_id' => 10
                        ],
                        'aw_sarp2_subscription_option' => [
                            'profile_id' => 1,
                            'initial_fee' => null,
                            'trial_price' => null,
                            'regular_price' => null,
                            'currency_code' => null,
                            'created_date' => '01 May 2020',
                            'trial_start_date' => '10 May 2020',
                            'regular_start_date' => '20 May 2020',
                            'regular_stop_date' => '30 Jun 2020'
                        ]
                    ],
                    'isAdmin' => false,
                    'orderItemId' => 1,
                    'subscriptionStartDate' => '01 May 2020',
                    'isTrialEnabled' => true,
                    'trialLabel' => 'Trial Label',
                    'trialPriceAndCycles' => '1 x $10',
                    'trialStartDate' => '10 May 2020',
                    'regularLabel' => 'Regular Label',
                    'stopLabel' => 'Subscription Ends',
                    'regularPriceAndCycles' => '2 x $20',
                    'regularStartDate' => '20 May 2020',
                    'regularStopDate' => '30 Jun 2020'
                ],
                'result' => [
                    'aw_sarp2_subscription_plan' => [
                        'plan_id' => 10
                    ],
                    'aw_sarp2_subscription_option' => [
                        'profile_id' => 1,
                        'initial_fee' => null,
                        'trial_price' => null,
                        'regular_price' => null,
                        'currency_code' => null,
                        'created_date' => '01 May 2020',
                        'trial_start_date' => '10 May 2020',
                        'regular_start_date' => '20 May 2020',
                        'regular_stop_date' => '30 Jun 2020'
                    ],
                    'options' => [
                        [
                            'label' =>__('Subscription Plan'),
                            'value' => 'Store Plan Name',
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Subscription Created On'),
                            'value' => '01 May 2020',
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Trial Label'),
                            'value' => __('%1 starting %2', [
                                '1 x $10',
                                '10 May 2020'
                            ]),
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Regular Label'),
                            'value' => __('%1 starting %2', [
                                '2 x $20',
                                '20 May 2020'
                            ]),
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Subscription Ends'),
                            'value' => __('30 Jun 2020'),
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ]
                    ]
                ]
            ],
            [
                'input' => [
                    'planName' => 'Plan Name',
                    'planTitle' => 'Store Plan Name',
                    'options' => [
                        'aw_sarp2_subscription_plan' => [
                            'plan_id' => 10
                        ],
                        'aw_sarp2_subscription_option' => [
                            'profile_id' => 1,
                            'initial_fee' => null,
                            'trial_price' => null,
                            'regular_price' => null,
                            'currency_code' => null,
                            'created_date' => '01 May 2020',
                            'trial_start_date' => '10 May 2020',
                            'regular_start_date' => '20 May 2020',
                            'regular_stop_date' => '30 Jun 2020'
                        ]
                    ],
                    'isAdmin' => true,
                    'orderItemId' => 1,
                    'subscriptionStartDate' => '01 May 2020',
                    'isTrialEnabled' => false,
                    'trialLabel' => 'Trial Label',
                    'trialPriceAndCycles' => '1 x $10',
                    'trialStartDate' => '10 May 2020',
                    'regularLabel' => 'Regular Label',
                    'stopLabel' => 'Subscription Ends',
                    'regularPriceAndCycles' => '2 x $20',
                    'regularStartDate' => '20 May 2020',
                    'regularStopDate' => '30 Jun 2020',
                    'subscriptionProfileLink' => 'http://store.com/admin/aw_sarp2/subscription/view/profile_id/1'
                ],
                'result' => [
                    'aw_sarp2_subscription_plan' => [
                        'plan_id' => 10
                    ],
                    'aw_sarp2_subscription_option' => [
                        'profile_id' => 1,
                        'initial_fee' => null,
                        'trial_price' => null,
                        'regular_price' => null,
                        'currency_code' => null,
                        'created_date' => '01 May 2020',
                        'trial_start_date' => '10 May 2020',
                        'regular_start_date' => '20 May 2020',
                        'regular_stop_date' => '30 Jun 2020'
                    ],
                    'options' => [
                        [
                            'label' =>__('Subscription Profile ID'),
                            'value' => 'http://store.com/admin/aw_sarp2/subscription/view/profile_id/1',
                            'aw_sarp2_subscription_plan' => 10,
                            'custom_view' => true,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Subscription Plan'),
                            'value' => 'Plan Name',
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Subscription Created On'),
                            'value' => '01 May 2020',
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Regular Label'),
                            'value' => __('%1 starting %2', [
                                '2 x $20',
                                '20 May 2020'
                            ]),
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ],
                        [
                            'label' => __('Subscription Ends'),
                            'value' => __('30 Jun 2020'),
                            'aw_sarp2_subscription_plan' => 10,
                            'option_id' => null,
                            'option_value' => null
                        ]
                    ]
                ]
            ],
            [
                'input' => [
                    'planName' => 'Plan Name',
                    'planTitle' => 'Store Plan Name',
                    'options' => [],
                    'isAdmin' => false,
                    'orderItemId' => 1,
                    'subscriptionStartDate' => '01 May 2020',
                    'isTrialEnabled' => true,
                    'trialLabel' => 'Trial Label',
                    'trialPriceAndCycles' => '1 x $10',
                    'trialStartDate' => '10 May 2020',
                    'regularLabel' => 'Regular Label',
                    'stopLabel' => 'Subscription Ends',
                    'regularPriceAndCycles' => '2 x $20',
                    'regularStartDate' => '20 May 2020',
                    'regularStopDate' => '30 Jun 2020'
                ],
                'result' => [
                    'options' => []
                ]
            ],
            [
                'input' => [
                    'planName' => 'Plan Name',
                    'planTitle' => 'Store Plan Name',
                    'options' => [
                        'aw_sarp2_subscription_plan' => null
                    ],
                    'isAdmin' => false,
                    'orderItemId' => 1,
                    'subscriptionStartDate' => '01 May 2020',
                    'isTrialEnabled' => true,
                    'trialLabel' => 'Trial Label',
                    'trialPriceAndCycles' => '1 x $10',
                    'trialStartDate' => '10 May 2020',
                    'regularLabel' => 'Regular Label',
                    'stopLabel' => 'Subscription Ends',
                    'regularPriceAndCycles' => '2 x $20',
                    'regularStartDate' => '20 May 2020',
                    'regularStopDate' => '30 Jun 2020'
                ],
                'result' => [
                    'aw_sarp2_subscription_plan' => null,
                    'options' => []
                ]
            ],
        ];
    }

    /**
     * Test removeSubscriptionOptions method
     *
     * @param array $options
     * @param array $expectedResult
     * @dataProvider removeSubscriptionOptionsDataProvider
     */
    public function testRemoveSubscriptionOptions($options, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->processor->removeSubscriptionOptions($options));
    }

    /**
     * @return array
     */
    public function removeSubscriptionOptionsDataProvider()
    {
        return [
            [
                'options' => [],
                'expectedResult' => [],
            ],
            [
                'options' => [
                    0 => [
                        'label' => 'Option 1',
                        'value' => 1,
                    ],
                    1 => [
                        'label' => 'Subscription Plan',
                        'value' => 'Test Plan Name',
                        'aw_sarp2_subscription_plan' => 1,
                    ],
                    2 => [
                        'label' => 'Option 2',
                        'value' => 2,
                    ],
                ],
                'expectedResult' => [
                    0 => [
                        'label' => 'Option 1',
                        'value' => 1,
                    ],
                    2 => [
                        'label' => 'Option 2',
                        'value' => 2,
                    ],
                ],
            ],
            [
                'options' => [
                    0 => [
                        'label' => 'Option 1',
                        'value' => 1,
                    ],
                    1 => [
                        'label' => 'Subscription Plan',
                        'value' => 'Test Plan Name',
                        'aw_sarp2_subscription_plan' => 1,
                    ],
                ],
                'expectedResult' => [
                    0 => [
                        'label' => 'Option 1',
                        'value' => 1,
                    ],
                ],
            ],
            [
                'options' => [
                    0 => [
                        'label' => 'Subscription Plan',
                        'value' => 'Test Plan Name',
                        'aw_sarp2_subscription_plan' => 1,
                    ],
                    1 => [
                        'label' => 'Option 2',
                        'value' => 2,
                    ],
                ],
                'expectedResult' => [
                    1 => [
                        'label' => 'Option 2',
                        'value' => 2,
                    ],
                ],
            ],
        ];
    }
}
