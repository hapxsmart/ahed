<?php
namespace Aheadworks\Sarp2\Engine\Notification\DataResolver;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class ResolveSubject
 * @package Aheadworks\Sarp2\Engine\Notification\DataResolver
 */
class ResolveSubject
{
    /**
     * @var PaymentInterface
     */
    private $sourcePayment;

    /**
     * @var PaymentInterface[]
     */
    private $nextPayments;

    /**
     * @var array
     */
    private $additionalData;

    /**
     * @param $sourcePayment
     * @param array $nextPayments
     * @param array $additionalData
     */
    public function __construct(
        $sourcePayment,
        array $nextPayments = [],
        array $additionalData = []
    ) {
        $this->sourcePayment = $sourcePayment;
        $this->nextPayments = $nextPayments;
        $this->additionalData = $additionalData;
    }

    /**
     * Get source payment
     *
     * @return PaymentInterface
     */
    public function getSourcePayment()
    {
        return $this->sourcePayment;
    }

    /**
     * Get next scheduled payments
     *
     * @return PaymentInterface[]
     */
    public function getNextPayments()
    {
        return $this->nextPayments;
    }

    /**
     * Get additional data
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
