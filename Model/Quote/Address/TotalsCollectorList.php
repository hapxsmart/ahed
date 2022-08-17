<?php
namespace Aheadworks\Sarp2\Model\Quote\Address;

use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Quote\Address\Total\Replacer;
use Aheadworks\Sarp2\Model\Quote\Address\Total\ReplacerFactory;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Recalculation\SubtotalFactory as RecalculationSubtotalFactory;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Store\Model\StoreManagerInterface;

class TotalsCollectorList
{
    /**
     * @var CollectorFactory
     */
    private $totalCollectorFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TotalFactory
     */
    private $totalFactory;

    /**
     * @var ReplacerFactory
     */
    private $totalReplacerFactory;

    /**
     * @var AbstractTotal[]
     */
    private $collectors;

    /**
     * @var RecalculationSubtotalFactory
     */
    private $recalculationSubtotalFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $totalCodes = [
        'subtotal',
        'discount',
        'shipping',
        'grand_total',
        'tax',
        'tax_subtotal',
        'tax_shipping',
        'weee',
        'weee_tax',
        'aw_afptc',
        'aw_reward_points' // todo: M2SARP2-564 - Integration with Reward Points
    ];

    /**
     * @param CollectorFactory $totalCollectorFactory
     * @param StoreManagerInterface $storeManager
     * @param TotalFactory $totalFactory
     * @param ReplacerFactory $totalReplacerFactory
     * @param RecalculationSubtotalFactory $recalculationSubtotalFactory
     * @param Config $config
     * @param array $totalCodes
     */
    public function __construct(
        CollectorFactory $totalCollectorFactory,
        StoreManagerInterface $storeManager,
        TotalFactory $totalFactory,
        ReplacerFactory $totalReplacerFactory,
        RecalculationSubtotalFactory $recalculationSubtotalFactory,
        Config $config,
        $totalCodes = []
    ) {
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->storeManager = $storeManager;
        $this->totalFactory = $totalFactory;
        $this->totalReplacerFactory = $totalReplacerFactory;
        $this->recalculationSubtotalFactory = $recalculationSubtotalFactory;
        $this->config = $config;
        $this->totalCodes = array_merge($this->totalCodes, $totalCodes);
    }

    /**
     * Get totals collector list
     *
     * @param int $storeId
     * @return AbstractTotal[]
     */
    public function getCollectors($storeId)
    {
        if (!$this->collectors) {
            $totalCollector = $this->totalCollectorFactory->create(
                ['store' => $this->storeManager->getStore($storeId)]
            );
            foreach ($totalCollector->getCollectors() as $code => $collector) {
                if (preg_match('/^aw_sarp2/', (string)$code) || in_array($code, $this->totalCodes)) {
                    $this->collectors[$code] = $collector;
                } else {
                    /** @var Replacer $replacer */
                    $replacer = $this->totalReplacerFactory->create();
                    $replacer->setCode($code);
                    $this->collectors[$code] = $replacer;
                }
            }
            if ($this->config->isRecalculationOfTotalsEnabled($storeId)) {
                $this->collectors['subtotal'] = $this->recalculationSubtotalFactory
                    ->create()
                    ->setCode('subtotal');
            }
        }

        return $this->collectors;
    }
}
