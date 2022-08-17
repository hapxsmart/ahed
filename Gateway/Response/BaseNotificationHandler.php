<?php
namespace Aheadworks\Sarp2\Gateway\Response;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\Notification\Locator;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class BaseNotificationHandler
 *
 * @package Aheadworks\Sarp2\Gateway\Response
 */
class BaseNotificationHandler implements HandlerInterface
{
    /**
     * @var Locator
     */
    private $notificationLocator;

    /**
     * @var Persistence
     */
    private $notificationPersistence;

    /**
     * @param Locator $notificationLocator
     * @param Persistence $notificationPersistence
     */
    public function __construct(
        Locator $notificationLocator,
        Persistence $notificationPersistence
    ) {
        $this->notificationLocator = $notificationLocator;
        $this->notificationPersistence = $notificationPersistence;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);
        $order = $paymentDataObject->getOrder();

        /** @var Notification $notification */
        $notification = $this->notificationLocator->getNotification(
            NotificationInterface::TYPE_BILLING_SUCCESSFUL,
            $order->getId()
        );
        if ($notification) {
            $notification->setStatus(NotificationInterface::STATUS_READY);
            $this->notificationPersistence->save($notification);
        }
    }
}
