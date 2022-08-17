<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment as PaymentResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Persistence
 * @package Aheadworks\Sarp2\Engine\Payment
 */
class Persistence
{
    /**
     * @var PaymentResource
     */
    private $resource;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @param PaymentResource $resource
     * @param PaymentFactory $paymentFactory
     */
    public function __construct(
        PaymentResource $resource,
        PaymentFactory $paymentFactory
    ) {
        $this->resource = $resource;
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * Save payment
     *
     * @param Payment $payment
     * @return Payment
     * @throws CouldNotSaveException
     */
    public function save(Payment $payment)
    {
        try {
            $this->resource->save($payment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $payment;
    }

    /**
     * Delete payment
     *
     * @param Payment $payment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Payment $payment)
    {
        try {
            $this->resource->delete($payment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the payment: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * Perform mass payments saving
     *
     * @param Payment[] $payments
     * @return void
     * @throws CouldNotSaveException
     */
    public function massSave($payments)
    {
        try {
            $this->resource->massUpdate($payments);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Perform mass payments delete
     *
     * @param Payment[] $payments
     * @param bool $withSchedule
     * @throws CouldNotDeleteException
     */
    public function massDelete($payments, $withSchedule = true)
    {
        try {
            $this->resource->massDelete($payments, $withSchedule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }

    /**
     * Perform mass change of payments statuses
     *
     * @param Payment[] $payments
     * @param string $status
     * @throws CouldNotSaveException
     */
    public function massChangeStatus($payments, $status)
    {
        /**
         * @param Payment $payment
         * @return int
         */
        $closure = function ($payment) {
            return $payment->getId();
        };
        $paymentIds = array_map($closure, $payments);
        try {
            $this->resource->changeStatus($paymentIds, $status);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Perform mass change of payments types
     *
     * @param Payment[] $payments
     * @param string $type
     * @throws CouldNotSaveException
     */
    public function massChangeType($payments, $type)
    {
        /**
         * @param Payment $payment
         * @return int
         */
        $closure = function ($payment) {
            return $payment->getId();
        };
        $paymentIds = array_map($closure, $payments);
        try {
            $this->resource->changeType($paymentIds, $type);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Perform mass change of payments statuses and types
     *
     * @param Payment[] $payments
     * @param string $status
     * @param string $type
     * @throws CouldNotSaveException
     */
    public function massChangeStatusAndType($payments, $status, $type)
    {
        /**
         * @param Payment $payment
         * @return int
         */
        $closure = function ($payment) {
            return $payment->getId();
        };
        $paymentIds = array_map($closure, $payments);
        try {
            $this->resource->changeStatusAndType($paymentIds, $status, $type);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Get payment by id
     *
     * @param int $id
     * @return Payment
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        /** @var Payment $payment */
        $payment = $this->paymentFactory->create();
        $this->resource->load($payment, $id);
        if (!$payment->getId()) {
            throw NoSuchEntityException::singleField('id', $id);
        }
        return $payment;
    }
}
