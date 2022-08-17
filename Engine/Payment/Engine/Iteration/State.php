<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Iteration;

/**
 * Class State
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Iteration
 */
class State implements StateInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var int
     */
    private $tmzOffset;

    /**
     * @param int $storeId
     * @param string $paymentType
     * @param int $tmzOffset
     */
    public function __construct(
        $storeId,
        $paymentType,
        $tmzOffset
    ) {
        $this->storeId = $storeId;
        $this->paymentType = $paymentType;
        $this->tmzOffset = $tmzOffset;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimezoneOffset()
    {
        return $this->tmzOffset;
    }
}
