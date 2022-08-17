<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Checkout\Plugin;

use Aheadworks\Sarp2\Model\Checkout\Plugin\SuccessValidator;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Session\SuccessValidator as CheckoutSuccessValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\Sarp2\Model\Checkout\Plugin\SuccessValidator
 */
class SuccessValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SuccessValidator
     */
    private $plugin;

    /**
     * @var CheckoutSuccessValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $successValidatorMock;

    /**
     * @var HasSubscriptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCheckerMock;

    /**
     * @var CheckoutSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->successValidatorMock = $this->createMock(CheckoutSuccessValidator::class);
        $this->quoteCheckerMock = $this->createMock(HasSubscriptions::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->plugin = $objectManager->getObject(
            SuccessValidator::class,
            [
                'quoteChecker' => $this->quoteCheckerMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'quoteRepository' => $this->quoteRepositoryMock
            ]
        );
    }

    /**
     * @param bool $origCallResult
     * @param bool|null $hasSubscriptions
     * @param array|null $lastSuccessProfileIds
     * @param bool $expectedResult
     * @dataProvider aroundIsValidDataProvider
     */
    public function testAroundIsValid(
        $origCallResult,
        $hasSubscriptions,
        $lastSuccessProfileIds,
        $expectedResult
    ) {
        $quoteId = 1;
        $isProceedCalled = false;

        $quoteMock = $this->createMock(Quote::class);
        $proceedMock = function () use (&$isProceedCalled, $origCallResult) {
            $isProceedCalled = true;
            return $origCallResult;
        };

        $this->checkoutSessionMock->expects($this->atLeastOnce())
            ->method('__call')
            ->willReturnMap(
                [
                    ['getLastSuccessQuoteId', [], $quoteId],
                    ['getLastSuccessProfileIds', [], $lastSuccessProfileIds]
                ]
            );
        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);
        if ($origCallResult) {
            $this->quoteCheckerMock->expects($this->once())
                ->method('check')
                ->with($quoteMock)
                ->willReturn($hasSubscriptions);
        }
        $this->assertEquals(
            $expectedResult,
            $this->plugin->aroundIsValid($this->successValidatorMock, $proceedMock)
        );

        $this->assertTrue($isProceedCalled);
    }

    public function testAroundIsValidQuoteIdIsNull()
    {
        $this->checkoutSessionMock->expects($this->atLeastOnce())
            ->method('__call')
            ->with('getLastSuccessQuoteId', [])
            ->willReturn(null);
        $this->assertFalse(
            $this->plugin->aroundIsValid(
                $this->successValidatorMock,
                function () {
                }
            )
        );
    }

    /**
     * @return array
     */
    public function aroundIsValidDataProvider()
    {
        return [
            [false, null, null, false],
            [true, false, null, true],
            [true, true, null, false],
            [true, true, [1], true]
        ];
    }
}
