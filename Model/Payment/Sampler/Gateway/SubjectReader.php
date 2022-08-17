<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class SubjectReader
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway
 */
class SubjectReader
{
    const PAYMENT = 'payment';
    const AMOUNT = 'amount';
    const SAMPLER_PAYMENT = 'sampler_payment';
    const COMMAND = 'command';
    const RESPONSE = 'response';

    /**
     * Reads sampler payment data object from subject.
     *
     * Use for builders/handlers with SARP interfaces for PaymentDataObject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        if (!isset($subject[self::PAYMENT])
            || !$subject[self::PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        return $subject[self::PAYMENT];
    }

    /**
     * Reads sampler payment data object from subject
     *
     * Use for builders/handlers with Magento interfaces for PaymentDataObject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readSamplerPaymentDataObject(array $subject)
    {
        if (!isset($subject[self::SAMPLER_PAYMENT])
            || !$subject[self::SAMPLER_PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        return $subject[self::SAMPLER_PAYMENT];
    }

    /**
     * Reads command from subject
     *
     * @param array $subject
     * @return string
     */
    public function readCommand(array $subject)
    {
        if (!isset($subject[self::COMMAND])
            || empty($subject[self::COMMAND])
        ) {
            throw new \InvalidArgumentException('Command data object should be provided');
        }

        return $subject[self::COMMAND];
    }

    /**
     * Reads response NVP from subject
     *
     * @param array $subject
     * @return array
     */
    public function readResponse(array $subject)
    {
        if (!isset($subject[self::RESPONSE]) || !is_array($subject[self::RESPONSE])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        return $subject[self::RESPONSE];
    }
}
