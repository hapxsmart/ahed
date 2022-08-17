<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine;

use Aheadworks\Sarp2\Engine\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->config = $objectManager->getObject(
            Config::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsBundledPaymentsEnabled($value)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_USE_BUNDLED_PAYMENTS)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isBundledPaymentsEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testGetBundledFailureHandlingType($value)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_BUNDLE_FAILURE_HANDLING,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($value);
        $this->assertSame($value, $this->config->getBundledFailureHandlingType());
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
