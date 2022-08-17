<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action\Exception;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Strategy\DefaultStrategy;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\StrategyResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\Exception\StrategyResolver
 */
class StrategyResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StrategyResolver
     */
    private $resolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(StrategyResolver::class);
    }

    /**
     * Test getStrategy method
     */
    public function testGetStrategy()
    {
        $paymentMethod = 'some_payment';
        $defaultStrategyMock = $this->createMock(DefaultStrategy::class);

        $this->assertEquals(
            $defaultStrategyMock,
            $this->resolver->getStrategy($paymentMethod)
        );
    }
}
