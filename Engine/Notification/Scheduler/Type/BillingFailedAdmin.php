<?php
namespace Aheadworks\Sarp2\Engine\Notification\Scheduler\Type;

use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Engine\Notification\SchedulerInterface;
use Aheadworks\Sarp2\Engine\NotificationFactory;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Exception as ExceptionFormatter;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity\Payment as PaymentFormatter;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;

class BillingFailedAdmin implements SchedulerInterface
{
    const EXCEPTION = 'exception';

    /**
     * @var NotificationFactory
     */
    private $notificationFactory;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentFormatter
     */
    private $paymentFormatter;

    /**
     * @var ExceptionFormatter
     */
    private $exceptionFormatter;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param NotificationFactory $notificationFactory
     * @param Persistence $persistence
     * @param DateTime $dateTime
     * @param Config $config
     * @param PaymentFormatter $paymentFormatter
     * @param ExceptionFormatter $exceptionFormatter
     * @param Json $serializer
     */
    public function __construct(
        NotificationFactory $notificationFactory,
        Persistence $persistence,
        DateTime $dateTime,
        Config $config,
        PaymentFormatter $paymentFormatter,
        ExceptionFormatter $exceptionFormatter,
        Json $serializer
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->persistence = $persistence;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->paymentFormatter = $paymentFormatter;
        $this->exceptionFormatter = $exceptionFormatter;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(PaymentInterface $sourcePayment, array $additionalData)
    {
        $profile = $sourcePayment->getProfile();
        $storeId = $profile->getStoreId();
        $exception = $additionalData[self::EXCEPTION] ?? null;
        $notifications = [];
        $emails = $this->getEmails($storeId);

        foreach ($emails as $email) {
            $notification = $this->notificationFactory->create();
            $notification->setType(NotificationInterface::TYPE_BILLING_FAILED_ADMIN)
                ->setStatus(NotificationInterface::STATUS_READY)
                ->setEmail($email)
                ->setName(null)
                ->setScheduledAt($this->dateTime->formatDate(true))
                ->setStoreId($storeId)
                ->setProfileId($sourcePayment->getProfileId());

            $data = [
                'profileId' => $profile->getProfileId(),
                'incrementProfileId' => $profile->getIncrementId(),
                'paymentId' => $sourcePayment->getId(),
                'paymentDetails' => $this->getPaymentDetails($sourcePayment),
                'exceptionMessage' => $exception
                    ? $this->exceptionFormatter->format($exception)
                    : null
            ];
            $notification->setNotificationData($data);

            try {
                $this->persistence->save($notification);
                $notifications[] = $notification;
            } catch (CouldNotSaveException $exception) {
                continue;
            }
        }

        return $notifications;
    }

    /**
     * Retrieve receiver email
     *
     * @param int $storeId
     * @return array
     */
    private function getEmails(int $storeId)
    {
        $adminEmail = $this->config->getFailedBillingAdminEmail($storeId);
        return $adminEmail ? [$adminEmail] : $this->config->getFailedBillingBCCEmail($storeId);
    }

    /**
     * Retrieve payment details array
     *
     * @param $payment
     * @return array
     */
    private function getPaymentDetails($payment)
    {
        try {
            $detailsJson = $this->paymentFormatter->format($payment);
            $details = $this->serializer->unserialize($detailsJson);
        } catch (\Exception $exception) {
            $details = [];
        }

        return $details;
    }
}
