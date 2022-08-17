<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Notification\Checker;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Checker\IsSendable;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Notification\Checker\IsSendable
 */
class IsSendableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IsSendable
     */
    private $checker;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->checker = $objectManager->getObject(IsSendable::class);
    }

    /**
     * @param NotificationInterface|\PHPUnit_Framework_MockObject_MockObject $notificationMock
     * @param array $restrictionMap
     * @param bool $expectedResult
     * @dataProvider checkDataProvider
     */
    public function testCheck($notificationMock, $restrictionMap, $expectedResult)
    {
        $class = new \ReflectionClass($this->checker);

        $typeToProfileStatRestrictedMapProperty = $class->getProperty(
            'typeToProfileStatusesRestrictedMap'
        );
        $typeToProfileStatRestrictedMapProperty->setAccessible(true);
        $typeToProfileStatRestrictedMapProperty->setValue($this->checker, $restrictionMap);

        $this->assertEquals($expectedResult, $this->checker->check($notificationMock));
    }

    /**
     * @return array
     */
    public function checkDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(
                    NotificationInterface::class,
                    ['getType' => NotificationInterface::TYPE_BILLING_SUCCESSFUL]
                ),
                [NotificationInterface::TYPE_UPCOMING_BILLING => [Status::CANCELLED]],
                true
            ],
            [
                $this->createConfiguredMock(
                    NotificationInterface::class,
                    [
                        'getType' => NotificationInterface::TYPE_UPCOMING_BILLING,
                        'getProfileStatus' => Status::ACTIVE
                    ]
                ),
                [NotificationInterface::TYPE_UPCOMING_BILLING => [Status::CANCELLED]],
                true
            ],
            [
                $this->createConfiguredMock(
                    NotificationInterface::class,
                    [
                        'getType' => NotificationInterface::TYPE_UPCOMING_BILLING,
                        'getProfileStatus' => Status::CANCELLED
                    ]
                ),
                [NotificationInterface::TYPE_UPCOMING_BILLING => [Status::CANCELLED]],
                false
            ]
        ];
    }
}
