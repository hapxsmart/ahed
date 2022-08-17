<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Quote\Repository\InvalidData;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect\ResultInterface;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect\ResultFactory;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detector;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\ValidatorInterface;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\ValidatorList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detector
 */
class DetectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Detector
     */
    private $detector;

    /**
     * @var HasSubscriptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCheckerMock;

    /**
     * @var ValidatorList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorListMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->quoteCheckerMock = $this->createMock(HasSubscriptions::class);
        $this->validatorListMock = $this->createMock(ValidatorList::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->detector = $objectManager->getObject(
            Detector::class,
            [
                'quoteChecker' => $this->quoteCheckerMock,
                'validatorList' => $this->validatorListMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testDetectNoValidators()
    {
        $resultMock = $this->createMock(ResultInterface::class);

        $this->quoteCheckerMock->expects($this->once())
            ->method('check')
            ->with($this->quoteMock)
            ->willReturn(true);
        $this->validatorListMock->expects($this->once())
            ->method('getValidators')
            ->willReturn([]);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isInvalid' => false])
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->detector->detect($this->quoteMock));
    }

    public function testDetectValid()
    {
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $resultMock = $this->createMock(ResultInterface::class);

        $this->quoteCheckerMock->expects($this->once())
            ->method('check')
            ->with($this->quoteMock)
            ->willReturn(true);
        $this->validatorListMock->expects($this->once())
            ->method('getValidators')
            ->willReturn([$validatorMock]);
        $validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->quoteMock)
            ->willReturn(true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isInvalid' => false])
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->detector->detect($this->quoteMock));
    }

    public function testDetectInvalid()
    {
        $reason = ResultInterface::REASON_COUPON_ON_SUBSCRIPTION_CART;
        $errorMessage = 'Coupon can not be applied to the cart which contains subscription(s)';

        $validator1Mock = $this->createMock(ValidatorInterface::class);
        $validator2Mock = $this->createMock(ValidatorInterface::class);
        $resultMock = $this->createMock(ResultInterface::class);

        $this->quoteCheckerMock->expects($this->once())
            ->method('check')
            ->with($this->quoteMock)
            ->willReturn(true);
        $this->validatorListMock->expects($this->once())
            ->method('getValidators')
            ->willReturn([$validator1Mock, $validator2Mock]);
        $validator1Mock->expects($this->once())
            ->method('validate')
            ->with($this->quoteMock)
            ->willReturn(false);
        $validator2Mock->expects($this->never())
            ->method('validate')
            ->with($this->quoteMock);
        $validator1Mock->expects($this->once())
            ->method('getReason')
            ->willReturn($reason);
        $validator1Mock->expects($this->once())
            ->method('getErrorMessage')
            ->willReturn($errorMessage);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'isInvalid' => true,
                    'reason' => $reason,
                    'errorMessage' => $errorMessage
                ]
            )
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->detector->detect($this->quoteMock));
    }

    public function testDetectNonSubscriptionQuote()
    {
        $resultMock = $this->createMock(ResultInterface::class);

        $this->quoteCheckerMock->expects($this->once())
            ->method('check')
            ->with($this->quoteMock)
            ->willReturn(false);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isInvalid' => false])
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->detector->detect($this->quoteMock));
    }
}
