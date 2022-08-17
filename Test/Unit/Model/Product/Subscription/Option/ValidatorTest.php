<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Helper\Validator\EmptyValidator;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Validator;
use Aheadworks\Sarp2\Model\Plan\Source\Status as PlanStatus;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Locale\FormatInterface;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Option\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var EmptyValidator
     */
    private $emptyValidator;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var SubscriptionOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var FormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormatMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->planRepositoryMock = $this->createMock(PlanRepositoryInterface::class);
        $this->optionFactoryMock = $this->createMock(SubscriptionOptionInterfaceFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->localeFormatMock = $this->getMockForAbstractClass(FormatInterface::class);
        $this->emptyValidator = $objectManager->getObject(EmptyValidator::class);

        $this->validator = $objectManager->getObject(
            Validator::class,
            [
                'planRepository' => $this->planRepositoryMock,
                'optionFactory' => $this->optionFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'localeFormat' => $this->localeFormatMock,
                'emptyValidator' => $this->emptyValidator
            ]
        );
    }

    /**
     * @param array $subOptions
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($subOptions, $planDefinitionOptions, $expectedResult, $expectedMessages)
    {
        $subOptionMock = $this->createConfiguredMock(
            SubscriptionOptionInterface::class,
            $subOptions
        );

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($subOptionMock);

        $planDefinitionMock = $this->createConfiguredMock(
            PlanDefinitionInterface::class,
            $planDefinitionOptions
        );
        $planMock = $this->getMockForAbstractClass(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(PlanStatus::ENABLED);
        $planMock->expects($this->once())
            ->method('getDefinition')
            ->willReturn($planDefinitionMock);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subOptions['getPlanId'])
            ->willReturn($planMock);

        $this->localeFormatMock->expects($this->any())
            ->method('getNumber')
            ->will($this->returnArgument(0));

        $this->assertEquals($expectedResult, $this->validator->isValid($subOptions));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                [
                    'getOptionId' => 15,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => 10.0000,
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 1
                ],
                [
                    'getIsInitialFeeEnabled' => true,
                    'getIsTrialPeriodEnabled' => true,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => 15,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 1
                ],
                [
                    'getIsInitialFeeEnabled' => true,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Initial fee is required.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => -2,
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 1
                ],
                [
                    'getIsInitialFeeEnabled' => true,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Initial fee must be greater than 0.']
            ],
            [
                [
                    'getOptionId' => 15,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 1
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => -29.0000,
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Trial price must be equal or greater than 0.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '',
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Trial price is required.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '',
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '',
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => false,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => -29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => false,
                ],
                false,
                ['Regular price must be greater than 0.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => '',
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => false,
                ],
                false,
                ['Regular price is required.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => 10.0000,
                    'getTrialPrice' => 29.0000,
                    'getRegularPrice' => 29.0000,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => true,
                    'getIsTrialPeriodEnabled' => true,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '',
                    'getRegularPrice' => 29.070,
                    'getIsAutoTrialPrice' => 1,
                    'getIsAutoRegularPrice' => 1
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '',
                    'getRegularPrice' => 29.6000,
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 1
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => false,
                ],
                true,
                []
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '',
                    'getRegularPrice' => 'a29.0000',
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => false,
                ],
                false,
                ['Please enter a valid number for regular price.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '3s',
                    'getTrialPrice' => '',
                    'getRegularPrice' => '29.0000',
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => true,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Please enter a valid number for initial fee.', 'Trial price is required.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => '',
                    'getTrialPrice' => '1.002a3',
                    'getRegularPrice' => '29.0000',
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Please enter a valid number for trial price.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => 0,
                    'getTrialPrice' => '1.0',
                    'getRegularPrice' => 0,
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                false,
                ['Regular price must be greater than 0.']
            ],
            [
                [
                    'getOptionId' => null,
                    'getPlanId' => 1,
                    'getProductId' => 2,
                    'getWebsiteId' => 0,
                    'getInitialFee' => 0,
                    'getTrialPrice' => '1.0',
                    'getRegularPrice' => '0.0001',
                    'getIsAutoTrialPrice' => 0,
                    'getIsAutoRegularPrice' => 0
                ],
                [
                    'getIsInitialFeeEnabled' => false,
                    'getIsTrialPeriodEnabled' => true,
                ],
                true,
                []
            ]
        ];
    }
}
