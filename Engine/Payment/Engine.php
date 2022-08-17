<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\EngineInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\StatesGenerator;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Pool;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Framework\App\Area;

/**
 * Class Engine
 * @package Aheadworks\Sarp2\Engine\Payment
 */
class Engine implements EngineInterface
{
    /**
     * Maximum process cycles count
     */
    const MAX_PROCESS_CYCLES = 10;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Pool
     */
    private $processorPool;

    /**
     * @var StatesGenerator
     */
    private $iterationStatesGenerator;

    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    /**
     * @param PaymentsList $paymentsList
     * @param Pool $processorPool
     * @param StatesGenerator $iterationStatesGenerator
     * @param Cleaner $cleaner
     * @param LoggerInterface $logger
     * @param AppEmulation $appEmulation
     */
    public function __construct(
        PaymentsList $paymentsList,
        Pool $processorPool,
        StatesGenerator $iterationStatesGenerator,
        Cleaner $cleaner,
        LoggerInterface $logger,
        AppEmulation $appEmulation
    ) {
        $this->paymentsList = $paymentsList;
        $this->processorPool = $processorPool;
        $this->iterationStatesGenerator = $iterationStatesGenerator;
        $this->cleaner = $cleaner;
        $this->logger = $logger;
        $this->appEmulation = $appEmulation;
    }

    /**
     * {@inheritdoc}
     */
    public function processPayments($paymentIds)
    {
        $this->process($paymentIds);
    }

    /**
     * {@inheritdoc}
     */
    public function processPaymentsForToday()
    {
        $this->process();
    }

    /**
     * Process payments
     *
     * @param array|null $paymentIds
     * @return void
     */
    private function process($paymentIds = null)
    {
        $this->cleaner->reset();

        $resolved = false;
        $cycles = 0;
        while ($cycles < self::MAX_PROCESS_CYCLES && !$resolved) {
            $wasOutstanding = false;

            foreach ($this->iterationStatesGenerator->generate() as $state) {
                $this->appEmulation->startEnvironmentEmulation($state->getStoreId(), Area::AREA_FRONTEND);
                $paymentType = $state->getPaymentType();
                $payments = $this->paymentsList->getProcessablePaymentsForToday(
                    $paymentType,
                    $state->getStoreId(),
                    $state->getTimezoneOffset(),
                    $paymentIds
                );
                $processor = $this->processorPool->getProcessor($paymentType);
                try {
                    $processResult = $processor->process($payments);
                    if ($processResult->isOutstandingDetected()) {
                        $wasOutstanding = true;
                    }
                } catch (\Exception $e) {
                    $this->logger->traceProcessing(
                        LoggerInterface::ENTRY_UNEXPECTED_EXCEPTION,
                        ['payments' => $payments],
                        ['exception' => $e]
                    );
                }
                $this->appEmulation->stopEnvironmentEmulation();
            }
            if (!$wasOutstanding) {
                $resolved = true;
            }
            $cycles++;
        }

        $this->cleaner->cleanup();
    }
}
