<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\StatusApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\StatusApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Applier;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\Result;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Applier
 */
class ApplierTest extends TestCase
{
    /**
     * @var Applier
     */
    private $applier;

    /**
     * @var ResultFactory|MockObject
     */
    private $validationResultFactoryMock;

    /**
     * @var StatusApplierPool|MockObject
     */
    private $statusApplierPoolMock;

    /**
     * @var ValidatorComposite|MockObject
     */
    private $validatorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validationResultFactoryMock = $this->createMock(ResultFactory::class);
        $this->statusApplierPoolMock = $this->createMock(StatusApplierPool::class);
        $this->validatorMock = $this->createMock(ValidatorComposite::class);

        $this->applier = $objectManager->getObject(
            Applier::class,
            [
                'validationResultFactory' => $this->validationResultFactoryMock,
                'statusApplierPool' => $this->statusApplierPoolMock,
                'validator' => $this->validatorMock
            ]
        );
    }

    /**
     * @param string $status
     * @param bool $isReactivated
     * @dataProvider applyDataProvider
     */
    public function testApply($status)
    {
        /** @var ProfileInterface|MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        /** @var ActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(ActionInterface::class);
        $actionDataMock = $this->createMock(DataObject::class);
        $statusApplierMock = $this->getMockForAbstractClass(StatusApplierInterface::class);

        $actionMock->expects($this->any())
            ->method('getData')
            ->willReturn($actionDataMock);
        $actionDataMock->expects($this->any())
            ->method('__call')
            ->with('getStatus')
            ->willReturn($status);
        $this->statusApplierPoolMock->expects($this->once())
            ->method('getApplier')
            ->willReturn($statusApplierMock);
        $statusApplierMock->expects($this->once())
            ->method('apply');

        $this->applier->apply($profileMock, $actionMock);
    }

    /**
     * @param array $expectedResultData
     * @dataProvider validateDataProvider
     */
    public function testValidate(
        $expectedResultData
    ) {
        /** @var ProfileInterface|Profile|MockObject $profileMock */
        $profileMock = $this->createMock(Profile::class);
        /** @var ActionInterface|MockObject $actionMock */
        $actionMock = $this->createMock(ActionInterface::class);
        $validationResultMock = $this->createMock(Result::class);

        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->with($profileMock, $actionMock)
            ->willReturn($expectedResultData['isValid']);

        if (!$expectedResultData['isValid']) {
            $this->validatorMock->expects($this->once())
                ->method('getMessage')
                ->willReturn($expectedResultData['message']);
        }

        $this->validationResultFactoryMock->expects($this->once())
            ->method('create')
            ->with($expectedResultData)
            ->willReturn($validationResultMock);

        $this->assertSame($validationResultMock, $this->applier->validate($profileMock, $actionMock));
    }

    /**
     * @return array
     */
    public function applyDataProvider()
    {
        return [
            [Status::SUSPENDED],
            [Status::ACTIVE]
        ];
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                ['isValid' => true]
            ], [
                ['isValid' => false,
                'message' => 'Unable to perform action, subscription already has "Active" status.']
            ], [
                ['isValid' => false, 'message' => 'Profile status Expired is not allowed.']
            ]
        ];
    }
}
