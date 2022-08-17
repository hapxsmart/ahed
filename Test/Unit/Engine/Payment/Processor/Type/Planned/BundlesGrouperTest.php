<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor\Type\Planned;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Candidate;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\CandidateFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Generator;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\GroupResult;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\GroupResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\BundlesGrouper;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Matcher;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\BundlesGrouper
 */
class BundlesGrouperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BundlesGrouper
     */
    private $grouper;

    /**
     * @var GroupResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var CandidateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $candidateFactoryMock;

    /**
     * @var Matcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $matcherMock;

    /**
     * @var Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resultFactoryMock = $this->createMock(GroupResultFactory::class);
        $this->candidateFactoryMock = $this->createMock(CandidateFactory::class);
        $this->matcherMock = $this->createMock(Matcher::class);
        $this->generatorMock = $this->createMock(Generator::class);
        $this->grouper = $objectManager->getObject(
            BundlesGrouper::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'candidateFactory' => $this->candidateFactoryMock,
                'matcher' => $this->matcherMock,
                'generator' => $this->generatorMock
            ]
        );
    }

    /**
     * @param PaymentInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $paymentMocks
     * @param array $matcherMap
     * @param array $expectedBundledMocks
     * @param PaymentInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $expectedSingleMocks
     * @dataProvider groupDataProvider
     */
    public function testGroup($paymentMocks, $matcherMap, $expectedBundledMocks, $expectedSingleMocks)
    {
        $resultMock = $this->createMock(GroupResult::class);

        $this->matcherMock->expects($this->any())
            ->method('match')
            ->willReturnMap($matcherMap);

        $generateMap = [];
        $candidateFactoryCreateMap = [];
        $candidateMocks = [];
        foreach ($expectedBundledMocks as $bundledMocks) {
            $parentMock = $this->createMock(PaymentInterface::class);
            $generateMap[] = [$bundledMocks, $parentMock];

            $candidateMock = $this->createMock(Candidate::class);
            $candidateFactoryCreateMap[] = [
                ['parent' => $parentMock, 'children' => $bundledMocks],
                $candidateMock
            ];
            $candidateMocks[] = $candidateMock;
        }

        $expectedCalls = count($expectedBundledMocks);
        $this->generatorMock->expects($this->exactly($expectedCalls))
            ->method('generate')
            ->willReturnMap($generateMap);
        $this->candidateFactoryMock->expects($this->exactly($expectedCalls))
            ->method('create')
            ->willReturnMap($candidateFactoryCreateMap);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'singlePayments' => $expectedSingleMocks,
                    'bundledCandidates' => $candidateMocks
                ]
            )
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->grouper->group($paymentMocks));
    }

    /**
     * Get expectations map for matcher mock
     *
     * @param array $matchedMocksGroups
     * @param PaymentInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $allMocks
     * @return array
     */
    private function getMatcherMap($matchedMocksGroups, $allMocks)
    {
        $map = [];

        if (count($matchedMocksGroups)) {
            foreach ($matchedMocksGroups as $matchedMocks) {
                for ($index1 = 0; $index1 < count($matchedMocks); $index1++) {
                    for ($index2 = 0; $index2 < count($matchedMocks); $index2++) {
                        if ($index1 != $index2) {
                            $map[] = [$matchedMocks[$index1], $matchedMocks[$index2], true];
                        }
                    }
                }
                foreach ($matchedMocks as $matchedMock) {
                    foreach ($allMocks as $mock) {
                        if (!in_array($mock, $matchedMocks)) {
                            $map[] = [$matchedMock, $mock, false];
                        }
                    }
                }
            }
        } else {
            for ($index1 = 0; $index1 < count($allMocks); $index1++) {
                for ($index2 = 0; $index2 < count($allMocks); $index2++) {
                    if ($index1 != $index2) {
                        $map[] = [$allMocks[$index1], $allMocks[$index2], false];
                    }
                }
            }
        }

        return $map;
    }

    /**
     * @return array
     */
    public function groupDataProvider()
    {
        $paymentMocks = [];
        for ($index = 1; $index <= 10; $index++) {
            $paymentMocks[] = $this->createConfiguredMock(
                PaymentInterface::class,
                ['getId' => $index]
            );
        }
        return [
            [
                $paymentMocks,
                $this->getMatcherMap(
                    [
                        [$paymentMocks[0], $paymentMocks[1]],
                        [$paymentMocks[5], $paymentMocks[7]]
                    ],
                    $paymentMocks
                ),
                [
                    [$paymentMocks[0], $paymentMocks[1]],
                    [$paymentMocks[5], $paymentMocks[7]]
                ],
                [
                    $paymentMocks[2],
                    $paymentMocks[3],
                    $paymentMocks[4],
                    $paymentMocks[6],
                    $paymentMocks[8],
                    $paymentMocks[9]
                ]
            ],
            [
                $paymentMocks,
                $this->getMatcherMap(
                    [
                        [$paymentMocks[0], $paymentMocks[1], $paymentMocks[5]],
                        [$paymentMocks[6], $paymentMocks[7]],
                        [$paymentMocks[4], $paymentMocks[9]]
                    ],
                    $paymentMocks
                ),
                [
                    [$paymentMocks[0], $paymentMocks[1], $paymentMocks[5]],
                    [$paymentMocks[6], $paymentMocks[7]],
                    [$paymentMocks[4], $paymentMocks[9]]
                ],
                [$paymentMocks[2], $paymentMocks[3], $paymentMocks[8]]
            ],
            [
                $paymentMocks,
                $this->getMatcherMap([], $paymentMocks),
                [],
                $paymentMocks
            ],
            [
                $paymentMocks,
                $this->getMatcherMap([$paymentMocks], $paymentMocks),
                [$paymentMocks],
                []
            ]
        ];
    }
}
