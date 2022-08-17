<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\DataResolver;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubjectFactory;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\Notification\Scheduler\Pool;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Notification\Manager
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerPoolMock;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var DataResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataResolverMock;

    /**
     * @var ResolveSubjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolveSubjectFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->schedulerPoolMock = $this->createMock(Pool::class);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->dataResolverMock = $this->createMock(DataResolver::class);
        $this->resolveSubjectFactoryMock = $this->createMock(ResolveSubjectFactory::class);
        $this->manager = $objectManager->getObject(
            Manager::class,
            [
                'schedulerPool' => $this->schedulerPoolMock,
                'persistence' => $this->persistenceMock,
                'dataResolver' => $this->dataResolverMock,
                'resolveSubjectFactory' => $this->resolveSubjectFactoryMock
            ]
        );
    }

    public function testSchedule()
    {
        $type = NotificationInterface::TYPE_BILLING_SUCCESSFUL;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $schedulerMock = $this->createMock(SchedulerInterface::class);
        $notificationMock = $this->createMock(NotificationInterface::class);

        $this->schedulerPoolMock->expects($this->once())
            ->method('getScheduler')
            ->with($type)
            ->willReturn($schedulerMock);
        $schedulerMock->expects($this->once())
            ->method('schedule')
            ->with($paymentMock)
            ->willReturn($notificationMock);

        $this->assertSame($notificationMock, $this->manager->schedule($type, $paymentMock));
    }

    public function testUpdateNotificationData()
    {
        $subjectData = ['key' => 'value'];
        $notificationData = ['varName' => 'varValue'];

        /** @var NotificationInterface|\PHPUnit_Framework_MockObject_MockObject $notificationMock */
        $notificationMock = $this->createMock(Notification::class);
        $resolveSubjectMock = $this->createMock(ResolveSubject::class);

        $this->resolveSubjectFactoryMock->expects($this->once())
            ->method('create')
            ->with($subjectData)
            ->willReturn($resolveSubjectMock);
        $this->dataResolverMock->expects($this->once())
            ->method('resolve')
            ->with($resolveSubjectMock)
            ->willReturn($notificationData);
        $notificationMock->expects($this->once())
            ->method('setNotificationData')
            ->with($notificationData);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($notificationMock);

        $this->manager->updateNotificationData($notificationMock, $subjectData);
    }
}
