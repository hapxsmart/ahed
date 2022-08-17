<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class GroupResult
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle
 */
class GroupResult
{
    /**
     * @var PaymentInterface[]
     */
    private $singlePayments = [];

    /**
     * @var Candidate[]
     */
    private $bundledCandidates = [];

    /**
     * @param array $singlePayments
     * @param array $bundledCandidates
     */
    public function __construct(
        array $singlePayments = [],
        array $bundledCandidates = []
    ) {
        $this->singlePayments = $singlePayments;
        $this->bundledCandidates = $bundledCandidates;
    }

    /**
     * Get single payments
     *
     * @return PaymentInterface[]
     */
    public function getSinglePayments()
    {
        return $this->singlePayments;
    }

    /**
     * Get bundled payment candidates
     *
     * @return Candidate[]
     */
    public function getBundleCandidates()
    {
        return $this->bundledCandidates;
    }
}
