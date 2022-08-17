<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Checkout\Plugin;

use Aheadworks\Sarp2\Model\Checkout\Plugin\Session;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\Sarp2\Model\Checkout\Plugin\Session
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Session
     */
    private $plugin;

    /**
     * @var CheckoutSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var HasSubscriptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCheckerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteCheckerMock = $this->createMock(HasSubscriptions::class);
        $this->plugin = $objectManager->getObject(
            Session::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'quoteChecker' => $this->quoteCheckerMock
            ]
        );
    }

    /**
     * @param bool $hasSubscriptions
     * @param bool $isProfileIdsCleared
     * @dataProvider aroundClearQuoteDataProvider
     */
    public function testAroundClearQuote($hasSubscriptions, $isProfileIdsCleared)
    {
        $quoteId = 1;
        $isProceedCalled = false;

        $quoteMock = $this->createMock(Quote::class);
        $proceedMock = function () use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->quoteRepositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId)
            ->willReturn($quoteMock);
        $this->quoteCheckerMock->expects($this->once())
            ->method('check')
            ->with($quoteMock)
            ->willReturn($hasSubscriptions);
        if ($isProfileIdsCleared) {
            $this->checkoutSessionMock->expects($this->exactly(2))
                ->method('__call')
                ->willReturnMap(
                    [
                        ['getLastSuccessQuoteId', [], $quoteId],
                        ['setLastSuccessProfileIds', [null], $this->returnSelf()]
                    ]
                );
        } else {
            $this->checkoutSessionMock->expects($this->once())
                ->method('__call')
                ->with('getLastSuccessQuoteId', [])
                ->willReturn($quoteId);
        }

        $this->assertSame(
            $this->checkoutSessionMock,
            $this->plugin->aroundClearQuote($this->checkoutSessionMock, $proceedMock)
        );
        $this->assertTrue($isProceedCalled);
    }

    /**
     * @return array
     */
    public function aroundClearQuoteDataProvider()
    {
        return [[false, false], [true, true]];
    }
}
