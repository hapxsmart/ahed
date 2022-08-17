<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Profile;

use Aheadworks\Sarp2\Model\Profile\AddressRepository;
use Aheadworks\Sarp2\Model\Profile\Address;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Address as AddressResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Profile\AddressRepository
 */
class AddressRepositoryTest extends TestCase
{
    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var AddressResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ProfileAddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->createMock(AddressResource::class);
        $this->addressFactoryMock = $this->createMock(ProfileAddressInterfaceFactory::class);

        $this->addressRepository = $objectManager->getObject(
            AddressRepository::class,
            [
                'resource' => $this->resourceMock,
                'addressFactory' => $this->addressFactoryMock,
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $addressId = 1;
        $addressToSaveMock = $this->createMock(Address::class);
        $addressToSaveMock->expects($this->once())
            ->method('getAddressId')
            ->willReturn($addressId);
        $loadedAddressMock = $this->createMock(Address::class);
        $loadedAddressMock->expects($this->once())
            ->method('getAddressId')
            ->willReturn($addressId);

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($addressToSaveMock)
            ->willReturnSelf();
        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($loadedAddressMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($loadedAddressMock, $addressId);

        $this->assertSame($loadedAddressMock, $this->addressRepository->save($addressToSaveMock));
    }

    /**
     * Test save method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testSaveCouldNotSaveException()
    {
        $addressToSaveMock = $this->createMock(Address::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($addressToSaveMock)
            ->willThrowException(
                new \Exception('Error!')
            );
        $this->expectException(CouldNotSaveException::class);
        $this->addressRepository->save($addressToSaveMock);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $addressId = 1;
        $addressMock = $this->createMock(Address::class);
        $addressMock->expects($this->once())
            ->method('getAddressId')
            ->willReturn($addressId);

        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($addressMock, $addressId);

        $this->assertSame($addressMock, $this->addressRepository->get($addressId));
    }

    /**
     * Test get method if no address found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with addressId = 1
     */
    public function testGetNoSuchEntityException()
    {
        $addressId = 1;
        $addressMock = $this->createMock(Address::class);
        $addressMock->expects($this->once())
            ->method('getAddressId')
            ->willReturn(null);

        $this->addressFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($addressMock, $addressId);
        $this->expectException(NoSuchEntityException::class);
        $this->addressRepository->get($addressId);
    }
}
