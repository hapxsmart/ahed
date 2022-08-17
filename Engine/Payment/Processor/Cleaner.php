<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;

/**
 * Class Cleaner
 * @package Aheadworks\Sarp2\Engine\Payment\Processor
 */
class Cleaner
{
    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Payment[]
     */
    private $paymentsToDelete = [];

    /**
     * @param Persistence $persistence
     * @param LoggerInterface $logger
     */
    public function __construct(
        Persistence $persistence,
        LoggerInterface $logger
    ) {
        $this->persistence = $persistence;
        $this->logger = $logger;
    }

    /**
     * Reset cleaner
     *
     * @return void
     */
    public function reset()
    {
        $this->paymentsToDelete = [];
    }

    /**
     * Add removal candidate
     *
     * @param Payment $payment
     * @return void
     */
    public function add($payment)
    {
        $this->paymentsToDelete[$payment->getItemId()] = $payment;
        $this->logger->traceProcessing(
            LoggerInterface::ENTRY_PAYMENT_ADDED_TO_CLEANER,
            ['payment' => $payment]
        );
    }

    /**
     * Add removal candidates
     *
     * @param Payment[] $payments
     * @return void
     */
    public function addList($payments)
    {
        array_walk($payments, [$this, 'add']);
    }

    /**
     * Remove removal candidate
     *
     * @param int $paymentId
     * @return void
     */
    public function remove($paymentId)
    {
        if (isset($this->paymentsToDelete[$paymentId])) {
            unset($this->paymentsToDelete[$paymentId]);
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENT_REMOVED_FROM_CLEANER,
                ['paymentId' => $paymentId]
            );
        }
    }

    /**
     * Perform cleanup
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->paymentsToDelete) {
            try {
                $this->persistence->massDelete($this->paymentsToDelete, false);
                $this->logger->traceProcessing(
                    LoggerInterface::ENTRY_CLEANUP,
                    ['payments' => $this->paymentsToDelete]
                );
                $this->reset();
            } catch (\Exception $e) {
                $this->logger->traceProcessing(
                    LoggerInterface::ENTRY_CLEANUP_FAILED,
                    ['exception' => $e]
                );
            }
        }
    }
}
