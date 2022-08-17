<?php
namespace Aheadworks\Sarp2\Engine\Payment\Checker;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

class IsProcessable
{
    /**
     * @var array
     */
    private $typeToStatusesMap = [
        PaymentInterface::TYPE_PLANNED => [
            PaymentInterface::STATUS_PLANNED,
            PaymentInterface::STATUS_CANCELLED
        ],
        PaymentInterface::TYPE_LAST_PERIOD_HOLDER => [PaymentInterface::STATUS_PLANNED],
        PaymentInterface::TYPE_ACTUAL => [
            PaymentInterface::STATUS_PENDING
        ],
        PaymentInterface::TYPE_REATTEMPT => [
            PaymentInterface::STATUS_PENDING,
            PaymentInterface::STATUS_RETRYING
        ],
        PaymentInterface::TYPE_OUTSTANDING => [
            PaymentInterface::STATUS_PLANNED,
            PaymentInterface::STATUS_PENDING,
            PaymentInterface::STATUS_RETRYING
        ]
    ];

    /**
     * @var array
     */
    private $typeToProfileStatusesRestrictedMap = [
        PaymentInterface::TYPE_PLANNED => [
            Status::CANCELLED,
            Status::EXPIRED,
            Status::SUSPENDED
        ],
        PaymentInterface::TYPE_ACTUAL => [
            Status::CANCELLED,
            Status::EXPIRED,
            Status::SUSPENDED
        ],
        PaymentInterface::TYPE_REATTEMPT => [
            Status::CANCELLED,
            Status::EXPIRED
        ]
    ];

    /**
     * Check if payment is processable
     *
     * @param PaymentInterface $payment
     * @param string $paymentType
     * @return bool
     */
    public function check(PaymentInterface $payment, string $paymentType)
    {
        $availablePaymentStatuses = $this->getAvailablePaymentStatuses($paymentType);
        if (!in_array($payment->getPaymentStatus(), $availablePaymentStatuses)) {
            return false;
        }

        $disallowedProfileStatuses = isset($this->typeToProfileStatusesRestrictedMap[$paymentType])
            ? $this->typeToProfileStatusesRestrictedMap[$paymentType]
            : [];
        if (in_array($payment->getProfile()->getStatus(), $disallowedProfileStatuses)) {
            return false;
        }

        return true;
    }

    /**
     * Get available payment statuses
     *
     * @param string $paymentType
     * @return array
     */
    public function getAvailablePaymentStatuses(string $paymentType)
    {
        return isset($this->typeToStatusesMap[$paymentType])
            ? $this->typeToStatusesMap[$paymentType]
            : [];
    }
}
