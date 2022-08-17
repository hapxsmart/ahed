<?php
namespace Aheadworks\Sarp2\Gateway\BamboraApac\Response;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Locator;
use Aheadworks\Sarp2\Engine\Notification\Persistence;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Gateway\BamboraApac\SubjectReaderFactory;

/**
 * Class NotificationHandler
 *
 * @package Aheadworks\Sarp2\Gateway\BamboraApac\Response
 */
class NotificationHandler implements HandlerInterface
{
    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var Locator
     */
    private $notificationLocator;

    /**
     * @var Persistence
     */
    private $notificationPersistence;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param Locator $notificationLocator
     * @param Persistence $notificationPersistence
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        Locator $notificationLocator,
        Persistence $notificationPersistence
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->notificationLocator = $notificationLocator;
        $this->notificationPersistence = $notificationPersistence;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        if (!$subjectReader) {
            return null;
        }
        $paymentDO = $subjectReader->readPayment($handlingSubject);

        /** @var Notification $notification */
        $notification = $this->notificationLocator->getNotification(
            NotificationInterface::TYPE_BILLING_SUCCESSFUL,
            $paymentDO->getOrder()->getId()
        );
        if ($notification) {
            $notification->setStatus(NotificationInterface::STATUS_READY);
            $this->notificationPersistence->save($notification);
        }
    }
}
