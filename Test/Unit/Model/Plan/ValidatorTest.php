<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Helper\Validator\EmptyValidator;
use Aheadworks\Sarp2\Model\Plan\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Plan\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmptyValidator
     */
    private $emptyValidator;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var array
     */
    private $planExpectations = [
        'getStatus' => '1',
        'getName' => 'Plan',
        'getRegularPricePatternPercent' => '90',
        'getTrialPricePatternPercent' => '85',
        'getPriceRounding' => 2
    ];

    /**
     * @var array
     */
    private $definitionExpectations = [
        'getBillingPeriod' => 'day',
        'getBillingFrequency' => 1,
        'getTotalBillingCycles' => 10,
        'getIsInitialFeeEnabled' => true,
        'getIsTrialPeriodEnabled' => true,
        'getTrialTotalBillingCycles' => 2
    ];

    /**
     * @var array
     */
    private $titleExpectations = [
        'getStoreId' => 0,
        'getTitle' => 'Plan'
    ];

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->emptyValidator = $objectManager->getObject(EmptyValidator::class);

        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'emptyValidator' => $this->emptyValidator
            ]
        );
    }

    /**
     * @param PlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($planMock, $expectedResult, $expectedMessages)
    {
        $this->assertEquals($expectedResult, $this->validator->isValid($planMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create and configure plan mock
     *
     * @param array $expectations
     * @return PlanInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createPlanMock(array $expectations = [])
    {
        $expectations = array_merge($this->planExpectations, $expectations);
        if (!isset($expectations['getDefinition'])) {
            $expectations['getDefinition'] = $this->createDefinitionMock();
        }
        if (!isset($expectations['getTitles'])) {
            $expectations['getTitles'] = [$this->createTitleMock()];
        }
        return $this->createConfiguredMock(PlanInterface::class, $expectations);
    }

    /**
     * Create and configure plan definition mock
     *
     * @param array $expectations
     * @return PlanDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createDefinitionMock(array $expectations = [])
    {
        $expectations = array_merge($this->definitionExpectations, $expectations);
        return $this->createConfiguredMock(PlanDefinitionInterface::class, $expectations);
    }

    /**
     * Create and configure plan title mock
     *
     * @param array $expectations
     * @return PlanTitleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTitleMock(array $expectations = [])
    {
        $expectations = array_merge($this->titleExpectations, $expectations);
        return $this->createConfiguredMock(PlanTitleInterface::class, $expectations);
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [$this->createPlanMock(), true, []],
            [$this->createPlanMock(['getName' => '']), false, ['Name is required.']],
            [
                $this->createPlanMock(['getRegularPricePatternPercent' => '110']),
                false,
                ['Regular payment price percentage must be greater than 0.0001 and less or equal to 100']
            ],
            [
                $this->createPlanMock(
                    [
                        'getDefinition' => $this->createDefinitionMock(['getIsTrialPeriodEnabled' => false]),
                        'getTrialPricePatternPercent' => '110'
                    ]
                ),
                true,
                []
            ],
            [
                $this->createPlanMock(['getTrialPricePatternPercent' => '110']),
                false,
                ['Trial payment price percentage must be greater or equal to 0 and less or equal to 100.']
            ],
            [$this->createPlanMock(['getPriceRounding' => null]), false, ['Price rounding is required.']],
            [
                $this->createPlanMock(
                    ['getDefinition' => $this->createDefinitionMock(['getTotalBillingCycles' => 'text'])]
                ),
                false,
                ['Number of payments is not a number.']
            ],
            [
                $this->createPlanMock(
                    [
                        'getDefinition' => $this->createDefinitionMock(
                            ['getIsTrialPeriodEnabled' => false, 'getTrialTotalBillingCycles' => null]
                        )
                    ]
                ),
                true,
                []
            ],
            [
                $this->createPlanMock(
                    ['getDefinition' => $this->createDefinitionMock(['getTrialTotalBillingCycles' => null])]
                ),
                false,
                ['Number of trial payments is required.']
            ],
            [
                $this->createPlanMock(
                    [
                        'getDefinition' => $this->createDefinitionMock(
                            ['getIsTrialPeriodEnabled' => false, 'getTrialTotalBillingCycles' => 'text']
                        )
                    ]
                ),
                true,
                []
            ],
            [
                $this->createPlanMock(
                    [
                        'getDefinition' => $this->createDefinitionMock(['getTrialTotalBillingCycles' => 'text'])
                    ]
                ),
                false,
                ['Number of trial payments is not a number.']
            ],
            [
                $this->createPlanMock(
                    [
                        'getDefinition' => $this->createDefinitionMock(
                            ['getIsTrialPeriodEnabled' => false, 'getTrialTotalBillingCycles' => 0]
                        )
                    ]
                ),
                true,
                []
            ],
            [
                $this->createPlanMock(
                    [
                        'getDefinition' => $this->createDefinitionMock(['getTrialTotalBillingCycles' => 0])
                    ]
                ),
                false,
                ['Number of trial payments must be greater than 0.']
            ],
            [
                $this->createPlanMock(
                    [
                        'getTitles' => [
                            $this->createTitleMock(['getStoreId' => 1]),
                            $this->createTitleMock(['getStoreId' => 1])
                        ]
                    ]
                ),
                false,
                ['Duplicated store view in storefront descriptions found.']
            ],
            [
                $this->createPlanMock(
                    ['getTitles' => [$this->createTitleMock(['getTitle' => ''])]]
                ),
                false,
                ['Storefront title is required.']
            ],
            [
                $this->createPlanMock(
                    ['getTitles' => [$this->createTitleMock(['getStoreId' => 1])]]
                ),
                false,
                ['Default values of storefront descriptions (for All Store Views option) aren\'t set.']
            ]
        ];
    }
}
