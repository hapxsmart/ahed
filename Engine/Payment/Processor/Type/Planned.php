<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Config;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Candidate;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\BundlesGrouper;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Copy;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Exception\CouldNotSaveException;

class Planned implements ProcessorInterface
{
    /**
     * @var BundlesGrouper
     */
    private $bundlesGrouper;

    /**
     * @var Detector
     */
    private $outstandingDetector;

    /**
     * @var Copy
     */
    private $copyService;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var Config
     */
    private $engineConfig;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var IsProcessable
     */
    private $isProcessableChecker;

    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param BundlesGrouper $bundlesGrouper
     * @param Detector $outstandingDetector
     * @param Copy $copyService
     * @param Persistence $persistence
     * @param Config $engineConfig
     * @param PaymentFactory $paymentFactory
     * @param IsProcessable $isProcessableChecker
     * @param Cleaner $cleaner
     * @param LoggerInterface $logger
     * @param ProfileRepositoryInterface $profileRepository
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        BundlesGrouper $bundlesGrouper,
        Detector $outstandingDetector,
        Copy $copyService,
        Persistence $persistence,
        Config $engineConfig,
        PaymentFactory $paymentFactory,
        IsProcessable $isProcessableChecker,
        Cleaner $cleaner,
        LoggerInterface $logger,
        ProfileRepositoryInterface $profileRepository,
        ResultFactory $resultFactory
    ) {
        $this->bundlesGrouper = $bundlesGrouper;
        $this->outstandingDetector = $outstandingDetector;
        $this->copyService = $copyService;
        $this->persistence = $persistence;
        $this->engineConfig = $engineConfig;
        $this->paymentFactory = $paymentFactory;
        $this->isProcessableChecker = $isProcessableChecker;
        $this->cleaner = $cleaner;
        $this->logger = $logger;
        $this->profileRepository = $profileRepository;
        $this->resultFactory = $resultFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($payments)
    {
        $payments = $this->splitPaymentsByStatus(
            array_filter($payments, [$this, 'isProcessable'])
        );

        $this->processCancelled($payments[PaymentInterface::STATUS_CANCELLED]);
        $payments = $payments[PaymentInterface::STATUS_PLANNED];

        $outstandingDetect = $this->outstandingDetector->detect($payments);
        $outstandingPayments = $outstandingDetect->getOutstandingPayments();
        if (count($outstandingPayments)) {
            $this->persistence->massChangeType($outstandingPayments, PaymentInterface::TYPE_OUTSTANDING);
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PAYMENTS_TYPE_MASS_CHANGE,
                ['payments' => $payments],
                ['updatedPayments' => $outstandingPayments]
            );
        }

        $payments = $outstandingDetect->getTodayPayments();
        if ($payments) {
            if ($this->engineConfig->isBundledPaymentsEnabled()) {
                $bundled = $this->bundlesGrouper->group($payments);
                $this->processSingle($bundled->getSinglePayments());
                $this->processBundled($bundled->getBundleCandidates());
            } else {
                $this->processSingle($payments);
            }
        }

        return $this->resultFactory->create(
            ['isOutstandingDetected' => (count($outstandingPayments) > 0)]
        );
    }

    /**
     * Process single payments
     *
     * @param PaymentInterface[]|Payment[] $payments
     * @throws CouldNotSaveException
     * @return void
     */
    private function processSingle($payments)
    {
        $this->persistence->massChangeStatus($payments, PaymentInterface::STATUS_UNPROCESSABLE);
        foreach ($payments as $payment) {
            /** @var Payment $actual */
            $actual = $this->paymentFactory->create();
            $this->copyService->copyToSingle($payment, $actual);
            $actual->setType(PaymentInterface::TYPE_ACTUAL)
                ->setPaymentStatus(PaymentInterface::STATUS_PENDING);
            try {
                $this->persistence->save($actual);
                $this->logger->traceProcessing(
                    LoggerInterface::ENTRY_ACTUAL_PAYMENT_CREATED,
                    ['payment' => $payment],
                    ['actual' => $actual]
                );
                $this->cleaner->add($payment);
            } catch (\Exception $e) {
                $payment->setPaymentStatus(PaymentInterface::TYPE_PLANNED);
                $this->persistence->save($payment);
                $this->logger->traceProcessing(
                    LoggerInterface::ENTRY_ACTUAL_PAYMENT_CREATION_FAILED,
                    ['payment' => $payment],
                    [
                        'actual' => $actual,
                        'exception' => $e
                    ]
                );
                $this->cleaner->remove($payment->getId());
            }
        }
    }

    /**
     * Process bundled payments
     *
     * @param Candidate[] $candidates
     * @throws CouldNotSaveException
     * @return void
     */
    private function processBundled($candidates)
    {
        foreach ($candidates as $candidate) {
            $parentId = null;
            $parent = $candidate->getParent();
            $parent->setType(PaymentInterface::TYPE_ACTUAL)
                ->setPaymentStatus(PaymentInterface::STATUS_PENDING);

            $children = $candidate->getChildren();
            /** @var Payment $child */
            foreach ($children as $child) {
                $child->setPaymentStatus(PaymentInterface::STATUS_UNPROCESSABLE)
                    ->setParentItem($parent);

                try {
                    if ($parentId) {
                        $child->setParentId($parentId);
                    }
                    $this->persistence->save($child);
                    if (!$parentId) {
                        $parentId = $child->getParentId();
                    }
                } catch (\Exception $e) {
                    $child->setPaymentStatus(PaymentInterface::TYPE_PLANNED)
                        ->setParentId(null)
                        ->setParentItem(null);
                    $this->persistence->save($child);
                    $this->logger->traceProcessing(
                        LoggerInterface::ENTRY_ACTUAL_PAYMENT_CREATION_FAILED,
                        ['payments' => $children],
                        ['actual' => $parent]
                    );
                }
            }

            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_ACTUAL_PAYMENT_CREATED,
                ['payments' => $children],
                ['actual' => $parent]
            );
        }
    }

    /**
     * Process cancelled payments
     *
     * @param PaymentInterface[] $payments
     * @return void
     * @throws \Exception
     */
    private function processCancelled(array $payments)
    {
        foreach ($payments as $payment) {
            $profile = $payment->getProfile();
            if ($profile->getStatus() == Status::ACTIVE && $this->isExpired($profile->getMembershipActiveUntilDate())) {
                $profile->setStatus(Status::EXPIRED);
                $this->profileRepository->save($profile);
                $this->persistence->delete($payment);
            }
        }
    }

    /**
     * Is expired date for today
     *
     * @param string $dateOfExpiration
     * @return bool
     * @throws \Exception
     */
    private function isExpired(string $dateOfExpiration) {
        $today = (new \DateTime())->getTimestamp();
        $dateOfExpiration = (new \DateTime($dateOfExpiration))->getTimestamp();

        return $dateOfExpiration < $today;
    }

    /**
     * Check if payment is processable
     *
     * @param $payment
     * @return bool
     */
    private function isProcessable($payment)
    {
        return $this->isProcessableChecker->check($payment, PaymentInterface::TYPE_PLANNED);
    }

    /**
     * Split payments by status
     *
     * @param PaymentInterface[] $payments
     * @return array
     */
    private function splitPaymentsByStatus(array $payments)
    {
        $result = [
            PaymentInterface::STATUS_PLANNED => [],
            PaymentInterface::STATUS_CANCELLED => []
        ];
        foreach ($payments as $payment)
        {
            $result[$payment->getPaymentStatus()][] = $payment;
        }

        return $result;
    }
}
