<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Initial;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDateTime;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class InitialTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Payment\Generator\Type
 */
class InitialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Billing period
     */
    const BILLING_PERIOD = BillingPeriod::DAY;

    /**
     * Billing frequency
     */
    const BILLING_FREQUENCY = 1;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Initial
     */
    private $generator;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    /**
     * @var TokenResource
     */
    private $tokenResource;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->generator = $this->objectManager->create(Initial::class);
        $this->profileResource = $this->objectManager->create(ProfileResource::class);
        $this->tokenResource = $this->objectManager->create(TokenResource::class);
    }

    /**
     * @param string $startDate
     * @param PlanDefinitionInterface $planDefinition
     * @param PrePaymentInfoInterface $prepaymentsInfo
     * @param array $expectedPayments
     * @param array $expectedSchedule
     * @dataProvider generateDataProvider
     */
    public function testGenerate(
        $startDate,
        $planDefinition,
        $prepaymentsInfo,
        $expectedPayments,
        $expectedSchedule
    ) {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

        $profileId = $profile->getProfileId();

        $profile->setStartDate($startDate)
            ->setPlanDefinition($planDefinition)
            ->setPrePaymentInfo($prepaymentsInfo);

        /** @var Token $token */
        $token = $this->objectManager->create(Token::class);
        /** @var TokenResource $tokenResource */
        $this->tokenResource->load($token, 'braintree', PaymentTokenInterface::PAYMENT_METHOD);
        $tokeId = $token->getTokenId();

        /** @var CoreDateTime $coreDate */
        $coreDate = $this->objectManager->create(CoreDateTime::class);

        $source = $this->objectManager->create(Source::class, ['profile' => $profile]);
        $payments = $this->generator->generate($source);

        $this->assertCount(count($expectedPayments), $payments);
        foreach ($expectedPayments as $index => $expectedPayment) {
            /** @var Payment $payment */
            $payment = $payments[$index];

            $this->assertEquals($profileId, $payment->getProfileId());
            $this->assertEquals(PaymentInterface::TYPE_PLANNED, $payment->getType());
            $this->assertEquals(PaymentInterface::STATUS_PLANNED, $payment->getPaymentStatus());
            $this->assertEquals(null, $payment->getPaidAt());
            $this->assertEquals(null, $payment->getRetryAt());
            $this->assertEquals(null, $payment->getTotalPaid());
            $this->assertEquals(null, $payment->getBaseTotalPaid());
            $this->assertEquals(['token_id' => $tokeId], $payment->getPaymentData());

            foreach ($expectedPayment as $method => $expectedValue) {
                if ($method == 'getScheduledAt') {
                    $this->assertEquals(
                        $expectedValue,
                        $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, $payment->$method())
                    );
                } else {
                    $this->assertEquals($expectedValue, $payment->$method());
                }
            }

            $schedule = $payment->getSchedule();
            $this->assertNotNull($schedule);

            $this->assertEquals(self::BILLING_PERIOD, $schedule->getPeriod());
            $this->assertEquals(self::BILLING_FREQUENCY, $schedule->getFrequency());

            foreach ($expectedSchedule as $method => $expectedValue) {
                $this->assertEquals($expectedValue, $schedule->$method());
            }
        }
    }

    /**
     * @return array
     */
    public function generateDataProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        $coreDate = $objectManager->create(CoreDateTime::class);
        return [
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => true,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 5,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => false,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => false,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_INITIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'now'),
                        'getTotalScheduled' => 5.00,
                        'getBaseTotalScheduled' => 7.25
                    ],
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'now'),
                        'getTotalScheduled' => 10.00,
                        'getBaseTotalScheduled' => 15.00
                    ]
                ],
                [
                    'isInitialPaid' => false,
                    'getTrialTotalCount' => 5,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => false,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => true,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 5,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => false,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => false,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'now'),
                        'getTotalScheduled' => 10.00,
                        'getBaseTotalScheduled' => 15.00
                    ]
                ],
                [
                    'isInitialPaid' => false,
                    'getTrialTotalCount' => 5,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => false,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 0,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => false,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => false,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_INITIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'now'),
                        'getTotalScheduled' => 5.00,
                        'getBaseTotalScheduled' => 7.25
                    ],
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'now'),
                        'getTotalScheduled' => 20.00,
                        'getBaseTotalScheduled' => 30.00
                    ]
                ],
                [
                    'isInitialPaid' => false,
                    'getTrialTotalCount' => 0,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => true,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 5,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => true,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => false,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'tomorrow'),
                        'getTotalScheduled' => 10.00,
                        'getBaseTotalScheduled' => 15.00
                    ]
                ],
                [
                    'isInitialPaid' => true,
                    'getTrialCount' => 0,
                    'getTrialTotalCount' => 5,
                    'getRegularCount' => 0,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => false,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 0,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => true,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => false,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'tomorrow'),
                        'getTotalScheduled' => 20.00,
                        'getBaseTotalScheduled' => 30.00
                    ]
                ],
                [
                    'isInitialPaid' => true,
                    'getTrialCount' => 0,
                    'getTrialTotalCount' => 0,
                    'getRegularCount' => 0,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => true,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 5,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => true,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => true,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'tomorrow'),
                        'getTotalScheduled' => 10.00,
                        'getBaseTotalScheduled' => 15.00
                    ]
                ],
                [
                    'isInitialPaid' => true,
                    'getTrialCount' => 1,
                    'getTrialTotalCount' => 5,
                    'getRegularCount' => 0,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => true,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 1,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => true,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => true,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'tomorrow'),
                        'getTotalScheduled' => 20.00,
                        'getBaseTotalScheduled' => 30.00
                    ]
                ],
                [
                    'isInitialPaid' => true,
                    'getTrialCount' => 1,
                    'getTrialTotalCount' => 1,
                    'getRegularCount' => 0,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => true,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => false,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 0,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => true,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => false,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => true
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'tomorrow'),
                        'getTotalScheduled' => 20.00,
                        'getBaseTotalScheduled' => 30.00
                    ]
                ],
                [
                    'isInitialPaid' => true,
                    'getTrialCount' => 0,
                    'getTrialTotalCount' => 0,
                    'getRegularCount' => 1,
                    'getRegularTotalCount' => 10
                ]
            ],
            [
                $coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now'),
                $objectManager->create(
                    PlanDefinitionInterface::class,
                    [
                        'data' => [
                            PlanDefinitionInterface::BILLING_FREQUENCY => self::BILLING_FREQUENCY,
                            PlanDefinitionInterface::BILLING_PERIOD => self::BILLING_PERIOD,
                            PlanDefinitionInterface::IS_INITIAL_FEE_ENABLED => false,
                            PlanDefinitionInterface::IS_TRIAL_PERIOD_ENABLED => true,
                            PlanDefinitionInterface::TRIAL_TOTAL_BILLING_CYCLES => 5,
                            PlanDefinitionInterface::TOTAL_BILLING_CYCLES => 10
                        ]
                    ]
                ),
                $objectManager->create(
                    PrePaymentInfoInterface::class,
                    [
                        'data' => [
                            PrePaymentInfoInterface::IS_INITIAL_FEE_PAID => false,
                            PrePaymentInfoInterface::IS_TRIAL_PAID => true,
                            PrePaymentInfoInterface::IS_REGULAR_PAID => false
                        ]
                    ]
                ),
                [
                    [
                        'getPaymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                        'getScheduledAt' => $coreDate->gmtDate(DateTime::DATE_PHP_FORMAT, 'tomorrow'),
                        'getTotalScheduled' => 10.00,
                        'getBaseTotalScheduled' => 15.00
                    ]
                ],
                [
                    'isInitialPaid' => false,
                    'getTrialCount' => 1,
                    'getTrialTotalCount' => 5,
                    'getRegularCount' => 0,
                    'getRegularTotalCount' => 10
                ]
            ]
        ];
    }
}
