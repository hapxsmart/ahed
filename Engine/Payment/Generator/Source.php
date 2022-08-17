<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Source
 * @package Aheadworks\Sarp2\Engine\Payment\Generator
 */
class Source implements SourceInterface
{
    /**
     * @var ProfileInterface|null
     */
    private $profile;

    /**
     * @var PaymentInterface[]
     */
    private $payments;

    /**
     * @param ProfileInterface|null $profile
     * @param PaymentInterface[] $payments
     */
    public function __construct(
        $profile = null,
        array $payments = []
    ) {
        $this->profile = $profile;
        $this->payments = $payments;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
