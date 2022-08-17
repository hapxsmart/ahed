<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Plan\TitleResolver
 */
class TitleResolverTest extends TestCase
{
    /**
     * @var TitleResolver
     */
    private $titleResolver;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class
        );

        $this->titleResolver = $objectManager->getObject(
            TitleResolver::class,
            [
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test getTitle method
     *
     * @param string $planName
     * @param PlanTitleInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $titles
     * @param int|null $storeId
     * @param int $currentStoreId
     * @param string $result
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider getTitleDataProvider
     */
    public function testGetTitle($planName, $titles, $storeId, $currentStoreId, $result)
    {
        if (!$storeId) {
            $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
            $storeMock->expects($this->once())
                ->method('getId')
                ->willReturn($currentStoreId);
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willReturn($storeMock);
        }

        $planMock = $this->getPlanMock($planName, $titles);

        $this->assertEquals($result, $this->titleResolver->getTitle($planMock, $storeId));
    }

    /**
     * @return array
     */
    public function getTitleDataProvider()
    {
        $defaultTitleStoreId = Store::DEFAULT_STORE_ID;
        $defaultTitle = 'Default Plan Title';
        $titleStoreId = 1;
        $title = 'Plan Title';

        $defaultPlanTitleMock = $this->getPlanTitleMock($defaultTitle, $defaultTitleStoreId);
        $planTitle = $this->getPlanTitleMock($title, $titleStoreId);

        return [
            [
                'planName' => 'Plan Name',
                'titles' => [
                    $defaultPlanTitleMock,
                    $planTitle
                ],
                'storeId' => 1,
                'currentStoreId' => 1,
                'result' => 'Plan Title',
            ],
            [
                'planName' => 'Plan Name',
                'titles' => [
                    $defaultPlanTitleMock,
                    $planTitle
                ],
                'storeId' => 10,
                'currentStoreId' => 1,
                'result' => 'Default Plan Title',
            ],
            [
                'planName' => 'Plan Name',
                'titles' => [],
                'storeId' => 1,
                'currentStoreId' => 1,
                'result' => 'Plan Name',
            ],
            [
                'planName' => 'Plan Name',
                'titles' => [
                    $defaultPlanTitleMock,
                    $planTitle
                ],
                'storeId' => null,
                'currentStoreId' => 1,
                'result' => 'Plan Title',
            ],
        ];
    }

    /**
     * Get plan mock
     *
     * @param string $planName
     * @param PlanTitleInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $titles
     * @return PlanInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPlanMock($planName, $titles)
    {
        $planMock = $this->getMockForAbstractClass(PlanInterface::class);
        $planMock->expects($this->any())
            ->method('getName')
            ->willReturn($planName);
        $planMock->expects($this->any())
            ->method('getTitles')
            ->willReturn($titles);

        return $planMock;
    }

    /**
     * Get plan title mock
     *
     * @param string $title
     * @param int $storeId
     * @return PlanTitleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPlanTitleMock($title, $storeId)
    {
        $planTitleMock = $this->getMockForAbstractClass(PlanTitleInterface::class);
        $planTitleMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $planTitleMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);

        return $planTitleMock;
    }
}
