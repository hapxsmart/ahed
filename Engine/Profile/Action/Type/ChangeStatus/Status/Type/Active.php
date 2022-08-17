<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface as EngineLogger;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\StatusApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Config;

/**
 * Class Active
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type
 */
class Active implements StatusApplierInterface
{
    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var SourceFactory
     */
    private $generatorSourceFactory;

    /**
     * @var Next
     */
    private $generator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var EngineLogger
     */
    private $engineLogger;

    /**
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param SourceFactory $generatorSourceFactory
     * @param Next $generator
     * @param Config $config
     * @param EngineLogger $engineLogger
     */
    public function __construct(
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        SourceFactory $generatorSourceFactory,
        Next $generator,
        Config $config,
        EngineLogger $engineLogger
    ) {
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->generator = $generator;
        $this->config = $config;
        $this->engineLogger = $engineLogger;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $status = $action->getData()->getStatus();
        $profile->setStatus($status);
        $payments = array_merge(
            $this->paymentsList->getLastScheduled($profile->getProfileId()),
            $this->paymentsList->getLastCancelled($profile->getProfileId())
        );

        if (empty($payments)) {
            $this->processFailedProfile($profile);
        } else {
            $this->processCancelledProfile($profile, $payments);
        }
    }

    /**
     * Process cancelled profile
     *
     * @param ProfileInterface $profile
     * @param Payment[] $payments
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function processCancelledProfile(ProfileInterface $profile, array $payments)
    {
        foreach ($payments as $payment) {
            $payment->getSchedule()->setIsReactivated(true);
            if ($payment->getPaymentStatus() == Payment::STATUS_CANCELLED) {
                $payment->setPaymentStatus(Payment::STATUS_PLANNED);
            }
        }

        $this->paymentPersistence->massSave($payments);
    }

    /**
     * Process failed profile
     *
     * @param ProfileInterface $profile
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function processFailedProfile(ProfileInterface $profile)
    {
        $lastFailed = $this->paymentsList->getLastFailed($profile->getProfileId());

        /** @var Source $source */
        $source = $this->generatorSourceFactory->create([
            'payments' => $lastFailed->isBundled()
                ? $lastFailed->getChildItems()
                : [$lastFailed]
        ]);
        $nextPlanetPayments = $this->generator->generate($source);

        foreach ($nextPlanetPayments as $nextPayment) {
            $nextPayment->getSchedule()->setIsReactivated(true);
            $this->paymentPersistence->save($nextPayment);
        }

        if (!empty($nextPlanetPayments) && $this->config->isLogEnabled()) {
            $this->engineLogger->traceProcessing(
                EngineLogger::ENTRY_PAYMENTS_SCHEDULED,
                ['payment' => $lastFailed],
                ['scheduledPayments' => $nextPlanetPayments]
            );
        }
    }
}
