<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationFactory;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification as NotificationResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Notification\Persistence
 */
class PersistenceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var NotificationResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var NotificationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->createMock(NotificationResource::class);
        $this->notificationFactoryMock = $this->createMock(NotificationFactory::class);
        $this->persistence = $objectManager->getObject(
            Persistence::class,
            [
                'resource' => $this->resourceMock,
                'notificationFactory' => $this->notificationFactoryMock
            ]
        );
    }

    public function testSave()
    {
        /** @var Notification|\PHPUnit_Framework_MockObject_MockObject $notificationMock */
        $notificationMock = $this->createMock(Notification::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($notificationMock);
        $this->assertSame($notificationMock, $this->persistence->save($notificationMock));
    }
}
