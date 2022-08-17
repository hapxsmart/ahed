<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Candidate
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle
 */
class Candidate
{
    /**
     * @var PaymentInterface
     */
    private $parent;

    /**
     * @var PaymentInterface[]
     */
    private $children;

    /**
     * @param $parent
     * @param array $children
     */
    public function __construct(
        $parent,
        array $children
    ) {
        $this->parent = $parent;
        $this->children = $children;
    }

    /**
     * Get parent payment
     *
     * @return PaymentInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get child payments
     *
     * @return PaymentInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }
}
