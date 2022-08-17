<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Processing;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Action\ResultInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatterInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Exception as ExceptionFormatter;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Payment as PaymentFormatter;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Schedule as ScheduleFormatter;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Candidate as BundleCandidate;
use Aheadworks\Sarp2\Engine\Payment\Schedule;

/**
 * Class Base
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Processing
 */
class Base implements DataFormatterInterface
{
    /**
     * @var PaymentFormatter
     */
    private $paymentFormatter;

    /**
     * @var ScheduleFormatter
     */
    private $scheduleFormatter;

    /**
     * @var ExceptionFormatter
     */
    private $exceptionFormatter;

    /**
     * @param PaymentFormatter $paymentFormatter
     * @param ScheduleFormatter $scheduleFormatter
     * @param ExceptionFormatter $exceptionFormatter
     */
    public function __construct(
        PaymentFormatter $paymentFormatter,
        ScheduleFormatter $scheduleFormatter,
        ExceptionFormatter $exceptionFormatter
    ) {
        $this->paymentFormatter = $paymentFormatter;
        $this->scheduleFormatter = $scheduleFormatter;
        $this->exceptionFormatter = $exceptionFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function format($subject)
    {
        try {
            return $this->performFormat($subject);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Perform data formatting
     *
     * @param array $subject
     * @return string
     * @throws \InvalidArgumentException
     */
    private function performFormat($subject)
    {
        $resultParts = [];
        if (is_array($subject)) {
            if (isset($subject['payments'])) {
                $this->assertArrayOfInstanceTypes($subject['payments'], Payment::class);
                $resultParts[] = sprintf(
                    'Processing of %s payments',
                    $this->getFirstItem($subject['payments'])->getType()
                );
            } elseif (isset($subject['payment'])) {
                $this->assertInstanceOf($subject['payment'], Payment::class);
                $resultParts[] = sprintf('Processing of %s payments', $subject['payment']->getType());
            } elseif (isset($subject['paymentId'])) {
                $resultParts[] = sprintf('Processing of payment #%d', $subject['paymentId']);
            }

            if (isset($subject['entryType'])) {
                switch ($subject['entryType']) {
                    case LoggerInterface::ENTRY_PROFILE_SET_STATUS:
                        if (isset($subject['profile'])) {
                            $this->assertInstanceOf($subject['profile'], ProfileInterface::class);
                            $resultParts[] = sprintf(
                                'Profile #%d status set to \'%s\'',
                                $subject['profile']->getProfileId(),
                                $subject['profile']->getStatus()
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENTS_SCHEDULED:
                        if (isset($subject['scheduledPayments']) && is_array($subject['scheduledPayments'])) {
                            $this->assertArrayOfInstanceTypes($subject['scheduledPayments'], Payment::class);
                            $paymentsCount = count($subject['scheduledPayments']);
                            $paymentsInfoParts = array_map(
                                [$this->paymentFormatter, 'format'],
                                $subject['scheduledPayments']
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

                    case LoggerInterface::ENTRY_PAYMENT_STATUS_CHANGE:
                        if (isset($subject['payment'])) {
                            $this->assertInstanceOf($subject['payment'], Payment::class);

                            $paymentId = $subject['payment']->getId();
                            $status = $subject['payment']->getPaymentStatus();
                            if (isset($subject['exception'])) {
                                $this->assertInstanceOf($subject['exception'], \Exception::class);
                                $resultParts[] = sprintf(
                                    'Payment #%d status changed to \'%s\' due to exception %s with message \'%s\'',
                                    $paymentId,
                                    $status,
                                    get_class($subject['exception']),
                                    $subject['exception']->getMessage()
                                );
                            } else {
                                $resultParts[] = sprintf('Payment #%d status changed to \'%s\'', $paymentId, $status);
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENTS_STATUS_CHANGE:
                        if (isset($subject['updatedPayments'])) {
                            $this->assertArrayOfInstanceTypes($subject['updatedPayments'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payments (%s) statuses changed to \'%s\'',
                                $this->getPaymentIdsAsString($subject['updatedPayments']),
                                $this->getFirstItem($subject['updatedPayments'])->getPaymentStatus()
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENTS_TYPE_MASS_CHANGE:
                        if (isset($subject['updatedPayments'])) {
                            $this->assertArrayOfInstanceTypes($subject['updatedPayments'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payments (%s) types mass changed to \'%s\'',
                                $this->getPaymentIdsAsString($subject['updatedPayments']),
                                $this->getFirstItem($subject['updatedPayments'])->getType()
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENTS_STATUS_AND_TYPE_MASS_CHANGE:
                        if (isset($subject['updatedPayments'])) {
                            $this->assertArrayOfInstanceTypes($subject['updatedPayments'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payments (%s) types and statuses mass changed: '
                                . 'type set to \'%s\', status set to \'%s\'',
                                $this->getPaymentIdsAsString($subject['updatedPayments']),
                                $this->getFirstItem($subject['updatedPayments'])->getType(),
                                $this->getFirstItem($subject['updatedPayments'])->getPaymentStatus()
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_UPDATE:
                        if (isset($subject['updatedPayment'])) {
                            $this->assertInstanceOf($subject['updatedPayment'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payment #%d has been updated: %s',
                                $subject['updatedPayment']->getId(),
                                $this->paymentFormatter->format($subject['updatedPayment'])
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_UPDATE_FAILED:
                        if (isset($subject['updatedPayment'])) {
                            $this->assertInstanceOf($subject['updatedPayment'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payment #%d update failed',
                                $subject['updatedPayment']->getId()
                            );
                            if (isset($subject['exception'])) {
                                $this->assertInstanceOf($subject['exception'], \Exception::class);
                                $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_BUNDLED_PAYMENTS_DETECTED:
                        if (isset($subject['candidates'])) {
                            $this->assertArrayOfInstanceTypes($subject['candidates'], BundleCandidate::class);

                            $resultParts[] = 'Bundled payment candidates detected.';
                            $candidatesParts = [];
                            /** @var BundleCandidate $candidate */
                            foreach ($subject['candidates'] as $candidate) {
                                $candidatesParts[] = sprintf(
                                    'Parent details: %s, children: %s',
                                    $this->paymentFormatter->format($candidate->getParent()),
                                    $this->getPaymentIdsAsString($candidate->getChildren())
                                );
                            }
                            $resultParts[] = implode(', ', $candidatesParts);
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_ADDED_TO_CLEANER:
                        if (isset($subject['payment'])) {
                            $this->assertInstanceOf($subject['payment'], Payment::class);
                            $resultParts[] = sprintf('Payment #%d added to cleaner', $subject['payment']->getId());
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_REMOVED_FROM_CLEANER:
                        if (isset($subject['paymentId'])) {
                            $resultParts[] = sprintf('Payment #%d removed from cleaner', $subject['paymentId']);
                        }
                        break;

                    case LoggerInterface::ENTRY_CLEANUP:
                        if (isset($subject['payments'])) {
                            $this->assertArrayOfInstanceTypes($subject['payments'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payments (%s) removed as a part of cleanup',
                                $this->getPaymentIdsAsString($subject['payments'])
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_CLEANUP_FAILED:
                        $resultParts[] = 'Cleanup failed';
                        if (isset($subject['exception'])) {
                            $this->assertInstanceOf($subject['exception'], \Exception::class);
                            $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                        }
                        break;

                    case LoggerInterface::ENTRY_ACTUAL_PAYMENT_CREATED:
                        if (isset($subject['actual'])) {
                            $this->assertInstanceOf($subject['actual'], Payment::class);
                            if ($subject['actual']->isBundled() && isset($subject['payments'])) {
                                $resultParts[] = sprintf(
                                    'Actual payment has been created from payments: %s',
                                    $this->getPaymentIdsAsString($subject['payments'])
                                );
                            } elseif (isset($subject['payment'])) {
                                $this->assertInstanceOf($subject['payment'], Payment::class);
                                $resultParts[] = sprintf(
                                    'Actual payment has been created from payment #%d',
                                    $subject['payment']->getId()
                                );
                            }
                            $resultParts[] = sprintf(
                                'Actual payment details: %s',
                                $this->paymentFormatter->format($subject['actual'])
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_ACTUAL_PAYMENT_CREATION_FAILED:
                        if (isset($subject['actual'])) {
                            $this->assertInstanceOf($subject['actual'], Payment::class);
                            $resultParts[] = sprintf(
                                'Actual payment creation failed. Payment details: %s',
                                $this->paymentFormatter->format($subject['actual'])
                            );
                            if ($subject['actual']->isBundled() && isset($subject['payments'])) {
                                $resultParts[] = sprintf(
                                    'Source payments: %s',
                                    $this->getPaymentIdsAsString($subject['payments'])
                                );
                            } elseif (isset($subject['payment'])) {
                                $this->assertInstanceOf($subject['payment'], Payment::class);
                                $resultParts[] = sprintf(
                                    'Source payment: #%d',
                                    $subject['payment']->getId()
                                );
                            }
                            if (isset($subject['exception'])) {
                                $this->assertInstanceOf($subject['exception'], \Exception::class);
                                $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_REATTEMPT_CREATED:
                        if (isset($subject['payment']) && isset($subject['reattempt'])) {
                            $this->assertInstanceOf($subject['payment'], Payment::class);
                            $this->assertInstanceOf($subject['reattempt'], Payment::class);

                            $resultParts[] = sprintf(
                                'Reattempt payment has been created from payment #%d',
                                $subject['payment']->getId()
                            );
                            $resultParts[] = sprintf(
                                'Reattempt payment details: %s',
                                $this->paymentFormatter->format($subject['reattempt'])
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_REATTEMPT_CREATION_FAILED:
                        if (isset($subject['payment']) && isset($subject['reattempt'])) {
                            $this->assertInstanceOf($subject['payment'], Payment::class);
                            $this->assertInstanceOf($subject['reattempt'], Payment::class);

                            $resultParts[] = sprintf(
                                'Reattempt payment creation failed. Payment details: %s',
                                $this->paymentFormatter->format($subject['reattempt'])
                            );
                            if (isset($subject['exception'])) {
                                $this->assertInstanceOf($subject['exception'], \Exception::class);
                                $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_REATTEMPT_RESCHEDULED:
                        if (isset($subject['payment']) && isset($subject['date'])) {
                            $this->assertInstanceOf($subject['payment'], Payment::class);
                            $resultParts[] = sprintf(
                                'Reattempt payment #%d rescheduled to \'%s\'',
                                $subject['payment']->getId(),
                                $subject['date']
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_SUCCESSFUL:
                        if (isset($subject['payment'])) {
                            $this->assertInstanceOf($subject['payment'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payment #%d successful. Payment details: %s',
                                $subject['payment']->getId(),
                                $this->paymentFormatter->format($subject['payment'])
                            );

                            if (isset($subject['result'])) {
                                $this->assertInstanceOf($subject['result'], ResultInterface::class);
                                $resultParts[] = sprintf(
                                    'Order #%d created',
                                    $subject['result']->getOrder()->getEntityId()
                                );
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_PAYMENT_FAILED:
                        if (isset($subject['failedPayment'])) {
                            $this->assertInstanceOf($subject['failedPayment'], Payment::class);
                            $resultParts[] = sprintf(
                                'Payment #%d failed. Payment details: %s',
                                $subject['failedPayment']->getId(),
                                $this->paymentFormatter->format($subject['failedPayment'])
                            );
                            if (isset($subject['exception'])) {
                                $this->assertInstanceOf($subject['exception'], \Exception::class);
                                $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_INCREMENT_STATE:
                        if (isset($subject['schedule'])) {
                            $this->assertInstanceOf($subject['schedule'], Schedule::class);
                            $resultParts[] = sprintf(
                                'State incremented. Schedule details: %s',
                                $this->scheduleFormatter->format($subject['schedule'])
                            );
                        }
                        break;

                    case LoggerInterface::ENTRY_OUTSTANDING_PAYMENTS_DETECTED:
                        if (isset($subject['outstandingPayments'])) {
                            $this->assertArrayOfInstanceTypes($subject['outstandingPayments'], Payment::class);
                            $paymentsCount = count($subject['outstandingPayments']);
                            if ($paymentsCount > 1) {
                                $resultParts[] = sprintf(
                                    'Outstanding payments (%s) detected',
                                    $this->getPaymentIdsAsString($subject['outstandingPayments'])
                                );
                            } else {
                                $resultParts[] = sprintf(
                                    'Outstanding payment #%d detected',
                                    $this->getFirstItem($subject['outstandingPayments'])->getId()
                                );
                            }
                        }
                        break;

                    case LoggerInterface::ENTRY_UNEXPECTED_EXCEPTION:
                        if (isset($subject['exception'])) {
                            $this->assertInstanceOf($subject['exception'], \Exception::class);
                            $resultParts[] = $this->exceptionFormatter->format($subject['exception']);
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return implode(self::PARTS_DELIMITER, $resultParts);
    }

    /**
     * Get payment ids as string
     *
     * @param Payment[] $payments
     * @return string
     */
    public function getPaymentIdsAsString($payments)
    {
        $parts = [];

        /**
         * @param Payment $payment
         * @return void
         */
        $callback = function ($payment) use (&$parts) {
            $parts[] = '#' . $payment->getId();
        };
        array_walk($payments, $callback);

        return implode(', ', $parts);
    }

    /**
     * Asserts is a correct entity type
     *
     * @param object $entity
     * @param string $type
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assertInstanceOf($entity, $type)
    {
        if (!$entity instanceof $type) {
            throw new \InvalidArgumentException('Invalid entity type.');
        }
    }

    /**
     * Asserts is a array of correct entity type
     *
     * @param array $array
     * @param string $type
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assertArrayOfInstanceTypes($array, $type)
    {
        $exception = new \InvalidArgumentException('Invalid entity type.');
        if (!is_array($array)) {
            throw $exception;
        }

        $isCorrect = true;

        /**
         * @param mixed $item
         * @return void
         */
        $callback = function ($item) use (&$isCorrect, $type) {
            if (!$item instanceof $type) {
                $isCorrect = false;
            }
        };
        array_walk($array, $callback);
        if (!$isCorrect) {
            throw $exception;
        }
    }

    /**
     * Get first array item
     *
     * @param array $array
     * @return mixed
     */
    private function getFirstItem($array)
    {
        $values = array_values($array);
        return $values[0];
    }
}
