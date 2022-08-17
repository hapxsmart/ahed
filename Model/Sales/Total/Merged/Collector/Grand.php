<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector;

use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

/**
 * Class Grand
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector
 */
class Grand implements CollectorInterface
{
    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param Summator $grandSummator
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        Summator $grandSummator,
        PriceCurrency $priceCurrency
    ) {
        $this->grandSummator = $grandSummator;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Subject $subject)
    {
        $baseGrandTotal = $this->grandSummator->getSum();
        $subject->getOrder()
            ->setBaseGrandTotal($baseGrandTotal)
            ->setGrandTotal($this->priceCurrency->convert($baseGrandTotal));
    }
}
