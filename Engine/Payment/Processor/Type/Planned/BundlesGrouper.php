<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\CandidateFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Generator;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\GroupResult;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\GroupResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Matcher;

/**
 * Class BundlesGrouper
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned
 */
class BundlesGrouper
{
    /**
     * @var GroupResultFactory
     */
    private $resultFactory;

    /**
     * @var CandidateFactory
     */
    private $candidateFactory;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GroupResultFactory $resultFactory
     * @param CandidateFactory $candidateFactory
     * @param Matcher $matcher
     * @param Generator $generator
     * @param LoggerInterface $logger
     */
    public function __construct(
        GroupResultFactory $resultFactory,
        CandidateFactory $candidateFactory,
        Matcher $matcher,
        Generator $generator,
        LoggerInterface $logger
    ) {
        $this->resultFactory = $resultFactory;
        $this->candidateFactory = $candidateFactory;
        $this->matcher = $matcher;
        $this->generator = $generator;
        $this->logger = $logger;
    }

    /**
     * Group bundled payments
     *
     * @param PaymentInterface[] $payments
     * @return GroupResult
     */
    public function group($payments)
    {
        $iterated = array_values($payments);

        $singlePayments = [];
        $bundledCandidates = [];

        $bundledBase = [];

        $cyclesCount = 0;
        $maxCycles = count($payments) + 1;
        while (count($iterated) > 0 && $cyclesCount < $maxCycles) {
            /** @var PaymentInterface $base */
            $base = array_shift($iterated);
            $baseId = $base->getId();
            $bundledBase[$baseId] = [$base];
            $toUnset = [];

            for ($index = 0; $index < count($iterated); $index++) {
                if ($this->matcher->match($base, $iterated[$index])) {
                    $toUnset[] = $index;
                    $bundledBase[$baseId][] = $iterated[$index];
                }
            }
            if (!empty($toUnset)) {
                foreach ($toUnset as $index) {
                    unset($iterated[$index]);
                }
            }

            $cyclesCount++;
        }

        foreach ($bundledBase as $baseItem) {
            if (count($baseItem) > 1) {
                $bundledCandidates[] = $this->candidateFactory->create(
                    [
                        'parent' => $this->generator->generate($baseItem),
                        'children' => $baseItem
                    ]
                );
            } else {
                $singlePayments[] = $baseItem[0];
            }
        }

        if (count($bundledCandidates)) {
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_BUNDLED_PAYMENTS_DETECTED,
                ['payments' => $payments],
                ['candidates' => $bundledCandidates]
            );
        }

        return $this->resultFactory->create(
            [
                'singlePayments' => $singlePayments,
                'bundledCandidates' => $bundledCandidates
            ]
        );
    }
}
