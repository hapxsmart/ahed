<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Checkout;

use Aheadworks\Sarp2\Model\Checkout\ConfigProvider\MixedQuote;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Test for \Aheadworks\Sarp2\Model\Checkout\ConfigProvider\MixedQuote
 */
class MixedQuoteConfigProviderTest extends TestCase
{
    /**
     * @var MixedQuote
     */
    private $configProvider;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteCheckerMock = $this->createMock(HasSubscriptions::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);

        $this->configProvider = $objectManager->getObject(
            MixedQuote::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'quoteChecker' => $this->quoteCheckerMock,
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );
    }

    /**
     * Test getConfig method
     *
     * @param int $quoteId
     * @param bool $hasSubscriptionOnly
     * @param bool $hasBoth
     * @param array $expectedResult
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($quoteId, $hasSubscriptionOnly, $hasBoth, $expectedResult)
    {
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        if ($quoteId) {
            $cartMock = $this->createMock(CartInterface::class);
            $this->quoteRepositoryMock->expects($this->once())
                ->method('getActive')
                ->with($quoteId)
                ->willReturn($cartMock);

            $this->quoteCheckerMock->expects($this->once())
                ->method('checkHasSubscriptionsOnly')
                ->with($cartMock)
                ->willReturn($hasSubscriptionOnly);
            $this->quoteCheckerMock->expects($this->once())
                ->method('checkHasBoth')
                ->with($cartMock)
                ->willReturn($hasBoth);
        }

        $this->assertEquals($expectedResult, $this->configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'quoteId' => null,
                'hasSubscriptionOnly' => false,
                'hasBoth' => false,
                'expectedResult' => []
            ],
            [
                'quoteId' => 1,
                'hasSubscriptionOnly' => true,
                'hasBoth' => true,
                'expectedResult' => [
                    'isAwSarp2QuoteSubscription' => true,
                    'isAwSarp2QuoteMixed' => true,
                ]
            ],
            [
                'quoteId' => 1,
                'hasSubscriptionOnly' => false,
                'hasBoth' => true,
                'expectedResult' => [
                    'isAwSarp2QuoteSubscription' => false,
                    'isAwSarp2QuoteMixed' => true,
                ]
            ],
            [
                'quoteId' => 1,
                'hasSubscriptionOnly' => true,
                'hasBoth' => false,
                'expectedResult' => [
                    'isAwSarp2QuoteSubscription' => true,
                    'isAwSarp2QuoteMixed' => false,
                ]
            ],
            [
                'quoteId' => 1,
                'hasSubscriptionOnly' => false,
                'hasBoth' => false,
                'expectedResult' => [
                    'isAwSarp2QuoteSubscription' => false,
                    'isAwSarp2QuoteMixed' => false,
                ]
            ],
        ];
    }

    /**
     * Test getConfig method if no active cart found
     */
    public function testGetConfigNoActiveCart()
    {
        $quoteId = 1;

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())
            ->method('getId')
            ->willReturn($quoteId);
        $this->checkoutSessionMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($quoteId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->assertEquals([], $this->configProvider->getConfig());
    }
}
