<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Engine\Iteration;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\State;
use Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\StateFactory;
use Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\StatesGenerator;
use Aheadworks\Sarp2\Engine\Payment\Processor\Pool;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\StatesGenerator
 */
class StatesGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatesGenerator
     */
    private $generator;

    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processorPoolMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

    /**
     * @var StateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->processorPoolMock = $this->createMock(Pool::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->stateFactoryMock = $this->createMock(StateFactory::class);
        $this->generator = $objectManager->getObject(
            StatesGenerator::class,
            [
                'processorPool' => $this->processorPoolMock,
                'storeManager' => $this->storeManagerMock,
                'coreDate' => $this->coreDateMock,
                'localeDate' => $this->localeDateMock,
                'stateFactory' => $this->stateFactoryMock
            ]
        );
    }

    public function testGenerate()
    {
        $storeId = 1;
        $paymentType = PaymentInterface::TYPE_PLANNED;
        $timezone = 'Europe/London';
        $timezoneOffset = 10;

        $storeMock = $this->createMock(StoreInterface::class);
        $stateMock = $this->createMock(State::class);

        $this->processorPoolMock->expects($this->once())
            ->method('getConfiguredPaymentTypes')
            ->willReturn([$paymentType]);
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->localeDateMock->expects($this->once())
            ->method('getConfigTimezone')
            ->with($storeId, ScopeInterface::SCOPE_STORE)
            ->willReturn($timezone);
        $this->coreDateMock->expects($this->once())
            ->method('calculateOffset')
            ->with($timezone)
            ->willReturn($timezoneOffset);
        $this->stateFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'storeId' => $storeId,
                    'paymentType' => $paymentType,
                    'tmzOffset' => $timezoneOffset
                ]
            )
            ->willReturn($stateMock);

        $this->assertEquals([$stateMock], $this->generator->generate());
    }

    public function testGenerateCaching()
    {
        $class = new \ReflectionClass($this->generator);

        $stateMock = $this->createMock(State::class);

        $statesMapProp = $class->getProperty('states');
        $statesMapProp->setAccessible(true);
        $statesMapProp->setValue($this->generator, [$stateMock]);

        $this->stateFactoryMock->expects($this->never())
            ->method('create');

        $this->assertEquals([$stateMock], $this->generator->generate());
    }
}
