<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable
 */
class IsProcessableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IsProcessable
     */
    private $checker;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->checker = $objectManager->getObject(IsProcessable::class);
    }

    /**
     * @param array $typeToStatusesMap
     * @param array $typeToProfileStatusesRestrictedMap
     * @param PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock
     * @param string $paymentType
     * @param bool $expectedResult
     * @dataProvider checkDataProvider
     */
    public function testCheck(
        $typeToStatusesMap,
        $typeToProfileStatusesRestrictedMap,
        $paymentMock,
        $paymentType,
        $expectedResult
    ) {
        $class = new \ReflectionClass($this->checker);

        $typeToStatusesMapProp = $class->getProperty('typeToStatusesMap');
        $typeToStatusesMapProp->setAccessible(true);
        $typeToStatusesMapProp->setValue($this->checker, $typeToStatusesMap);

        $typeToProfileStatusesRestrictedMapProp = $class->getProperty('typeToProfileStatusesRestrictedMap');
        $typeToProfileStatusesRestrictedMapProp->setAccessible(true);
        $typeToProfileStatusesRestrictedMapProp->setValue(
            $this->checker,
            $typeToProfileStatusesRestrictedMap
        );

        $this->assertEquals($expectedResult, $this->checker->check($paymentMock, $paymentType));
    }

    /**
     * @param array $map
     * @param string $paymentType
     * @param array $expectedResult
     * @dataProvider getAvailablePaymentStatusesDataProvider
     */
    public function testGetAvailablePaymentStatuses($map, $paymentType, $expectedResult)
    {
        $class = new \ReflectionClass($this->checker);

        $typeToStatusesMapProp = $class->getProperty('typeToStatusesMap');
        $typeToStatusesMapProp->setAccessible(true);
        $typeToStatusesMapProp->setValue($this->checker, $map);

        $this->assertEquals(
            $expectedResult,
            $this->checker->getAvailablePaymentStatuses($paymentType)
        );
    }

    /**
     * @return array
     */
    public function checkDataProvider()
    {
        return [
            'processable' => [
                [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED]],
                [],
                $this->createConfiguredMock(
                    PaymentInterface::class,
                    [
                        'getPaymentStatus' => PaymentInterface::STATUS_PLANNED,
                        'getProfile' => $this->createConfiguredMock(
                            ProfileInterface::class,
                            ['getStatus' => Status::ACTIVE]
                        ),
                        'getType' => PaymentInterface::TYPE_PLANNED
                    ]
                ),
                PaymentInterface::TYPE_PLANNED,
                true
            ],
            'processable with profile status restriction' => [
                [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED]],
                [PaymentInterface::TYPE_PLANNED => [Status::SUSPENDED]],
                $this->createConfiguredMock(
                    PaymentInterface::class,
                    [
                        'getPaymentStatus' => PaymentInterface::STATUS_PLANNED,
                        'getProfile' => $this->createConfiguredMock(
                            ProfileInterface::class,
                            ['getStatus' => Status::ACTIVE]
                        ),
                        'getType' => PaymentInterface::TYPE_PLANNED
                    ]
                ),
                PaymentInterface::TYPE_PLANNED,
                true
            ],
            'unavailable payment status' => [
                [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED]],
                [],
                $this->createConfiguredMock(
                    PaymentInterface::class,
                    ['getPaymentStatus' => PaymentInterface::STATUS_UNPROCESSABLE]
                ),
                PaymentInterface::TYPE_PLANNED,
                false
            ],
            'unavailable profile status' => [
                [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED]],
                [PaymentInterface::TYPE_PLANNED => [Status::SUSPENDED]],
                $this->createConfiguredMock(
                    PaymentInterface::class,
                    [
                        'getPaymentStatus' => PaymentInterface::STATUS_PLANNED,
                        'getProfile' => $this->createConfiguredMock(
                            ProfileInterface::class,
                            ['getStatus' => Status::SUSPENDED]
                        )
                    ]
                ),
                PaymentInterface::TYPE_PLANNED,
                false
            ],
            'retrying payment' => [
                [PaymentInterface::TYPE_REATTEMPT => [PaymentInterface::STATUS_RETRYING]],
                [PaymentInterface::TYPE_REATTEMPT => [Status::CANCELLED]],
                $this->createConfiguredMock(
                    PaymentInterface::class,
                    [
                        'getPaymentStatus' => PaymentInterface::STATUS_RETRYING,
                        'getProfile' => $this->createConfiguredMock(
                            ProfileInterface::class,
                            ['getStatus' => Status::ACTIVE]
                        ),
                        'getType' => PaymentInterface::TYPE_REATTEMPT,
                        'getRetriesCount' => 0
                    ]
                ),
                PaymentInterface::TYPE_REATTEMPT,
                true
            ],
            'empty payment statuses map' => [
                [],
                [],
                $this->createConfiguredMock(
                    PaymentInterface::class,
                    ['getPaymentStatus' => PaymentInterface::STATUS_PLANNED]
                ),
                PaymentInterface::TYPE_PLANNED,
                false
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAvailablePaymentStatusesDataProvider()
    {
        return [
            [
                [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED]],
                PaymentInterface::TYPE_PLANNED,
                [PaymentInterface::STATUS_PLANNED]
            ],
            [
                [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED]],
                PaymentInterface::TYPE_ACTUAL,
                []
            ]
        ];
    }
}
