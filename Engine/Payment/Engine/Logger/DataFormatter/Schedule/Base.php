<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Schedule;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatterInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Exception as ExceptionFormatter;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Payment as PaymentFormatter;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class Base
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Schedule
 */
class Base implements DataFormatterInterface
{
    /**
     * @var PaymentFormatter
     */
    private $paymentFormatter;

    /**
     * @var ExceptionFormatter
     */
    private $exceptionFormatter;

    /**
     * @var Status
     */
    private $profileStatusSource;

    /**
     * @param PaymentFormatter $paymentFormatter
     * @param ExceptionFormatter $exceptionFormatter
     * @param Status $profileStatusSource
     */
    public function __construct(
        PaymentFormatter $paymentFormatter,
        ExceptionFormatter $exceptionFormatter,
        Status $profileStatusSource
    ) {
        $this->paymentFormatter = $paymentFormatter;
        $this->exceptionFormatter = $exceptionFormatter;
        $this->profileStatusSource = $profileStatusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function format($subject)
    {
        $resultParts = [];
        if (is_array($subject) && isset($subject['profileId'])) {
            $resultParts[] = sprintf('Profile #%d scheduling', $subject['profileId']);
            if (isset($subject['entryType'])) {
                switch ($subject['entryType']) {
                    case LoggerInterface::ENTRY_PROFILE_SET_STATUS:
                        if (isset($subject['profileStatus'])) {
                            $statusOptions = $this->profileStatusSource->getOptions();
                            if (isset($statusOptions[$subject['profileStatus']])) {
                                $resultParts[] = sprintf(
                                    'Profile status set to \'%s\'',
                                    $statusOptions[$subject['profileStatus']]
                                );
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENTS_SCHEDULED:
                        if (isset($subject['payments']) && is_array($subject['payments'])) {
                            $paymentsCount = count($subject['payments']);
                            $paymentsInfoParts = array_map(
                                [$this->paymentFormatter, 'format'],
                                $subject['payments']
                            );
                            $resultParts[] = $paymentsCount > 1
                                ? sprintf(
                                    '%d payments scheduled: %s',
                                    $paymentsCount,
                                    implode(', ', $paymentsInfoParts)
                                )
                                : sprintf('Payment scheduled: %s', implode(', ', $paymentsInfoParts));
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENTS_SCHEDULE_FAILED:
                        $resultParts[] = 'Scheduling has failed';
                        if (isset($subject['exception'])) {
                            $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                        }
                        if (isset($subject['payments'])
                            && is_array($subject['payments'])
                            && count($subject['payments'])
                        ) {
                            $paymentIds = [];

                            /**
                             * @param Payment $payment
                             * @return void
                             */
                            $callback = function ($payment) use (&$paymentIds) {
                                $paymentIds[] = $payment->getId();
                            };
                            array_walk($subject['payments'], $callback);

                            $resultParts[] = sprintf(
                                'Payments removed: %s',
                                implode(', ', $paymentIds)
                            );
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return implode(self::PARTS_DELIMITER, $resultParts);
    }
}
