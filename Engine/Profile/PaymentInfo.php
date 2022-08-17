<?php
namespace Aheadworks\Sarp2\Engine\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class PaymentInfo
 * @package Aheadworks\Sarp2\Engine\Profile
 */
class PaymentInfo implements PaymentInfoInterface
{
    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var string
     */
    private $paymentPeriod;

    /**
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     */
    public function __construct(
        $profile,
        $paymentPeriod
    ) {
        $this->profile = $profile;
        $this->paymentPeriod = $paymentPeriod;
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
    public function getPaymentPeriod()
    {
        return $this->paymentPeriod;
    }
}
