<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\DataResolver;

use Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate;
use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate
 */
class NextReattemptDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NextReattemptDate
     */
    private $resolver;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(
                $this->callback(
                    function ($argument) {
                        return $argument instanceof \DateTime;
                    }
                )
            )
            ->willReturnCallback(
                function ($date) {
                    return $date->format(DateTime::DATETIME_PHP_FORMAT);
                }
            );

        $this->configMock = $this->createMock(Config::class);

        $this->resolver = $objectManager->getObject(
            NextReattemptDate::class,
            [
                'dateTime' => $this->dateTimeMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * @param string $paymentDate
     * @param string $result
     * @dataProvider getDateNextDataProvider
     */
    public function testGetDateNext($paymentDate, $result)
    {
        $this->assertEquals($result, $this->resolver->getDateNext($paymentDate));
    }

    /**
     * @param string $paymentDate
     * @param int $reattemptsCount
     * @param string $result
     * @dataProvider getLastDateDataProvider
     */
    public function testGetLastDate($paymentDate, $reattemptsCount, $result)
    {
        $this->configMock->expects($this->once())
            ->method('getMaxRetriesCount')
            ->willReturn(3);

        $this->assertEquals($result, $this->resolver->getLastDate($paymentDate, $reattemptsCount));
    }

    /**
     * @return array
     */
    public function getDateNextDataProvider()
    {
        return [
            ['2017-08-01 14:01:08', '2017-08-02 14:01:08'],
            ['2017-08-02 14:01:08', '2017-08-03 14:01:08']
        ];
    }

    /**
     * @return array
     */
    public function getLastDateDataProvider()
    {
        return [
            ['2017-08-06 14:01:08', 0, '2017-08-09 14:01:08'],
            ['2017-08-06 14:01:08', 1, '2017-08-08 14:01:08'],
            ['2017-08-06 14:01:08', 2, '2017-08-07 14:01:08']
        ];
    }
}
