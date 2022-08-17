<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Plan\Source\Status as PlanStatus;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Checker;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Plan\Checker
 */
class CheckerTest extends TestCase
{
    /**
     * @var Checker
     */
    private $model;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->planRepositoryMock = $this->createMock(PlanRepositoryInterface::class);

        $this->model = $objectManager->getObject(
            Checker::class,
            [
                'planRepository' => $this->planRepositoryMock,
            ]
        );
    }

    /**
     * Test isEnabled method
     *
     * @param $status
     * @param bool $expectedResult
     * @dataProvider isEnabledDataProvider
     * @throws \ReflectionException
     */
    public function testIsEnabled($status, $expectedResult)
    {
        $planId = 125;

        $planMock =$this->createMock(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);

        $this->assertEquals($expectedResult, $this->model->isEnabled($planId));
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            [
                'status' => PlanStatus::ENABLED,
                'expectedResult' => true
            ],
            [
                'status' => PlanStatus::DISABLED,
                'expectedResult' => false
            ]
        ];
    }

    /**
     * Test isEnabled method if no plan found
     */
    public function testIsEnabledNoPlan()
    {
        $planId = 125;

        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willThrowException(new LocalizedException(__('No such entity!')));

        $this->assertFalse($this->model->isEnabled($planId));
    }
}
