<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Engine\Config;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\Collection;
use Aheadworks\Sarp2\Test\Integration\Engine\ConfigStub;
use Aheadworks\Sarp2\Test\Integration\Engine\Payment\Processor\Outstanding\DetectorStub;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class PlannedTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Payment\Processor\Type
 */
class PlannedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Planned
     */
    private $processor;

    /**
     * @var ConfigStub
     */
    private $configStub;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure(
            [
                'preferences' => [
                    Config::class => ConfigStub::class,
                    Detector::class => DetectorStub::class
                ]
            ]
        );
        $this->objectManager->removeSharedInstance(Config::class);
        $this->objectManager->removeSharedInstance(Detector::class);
        $this->processor = $this->objectManager->create(Planned::class);
        $this->configStub = $this->objectManager->get(ConfigStub::class);
    }

    public function testProcess()
    {
        $this->configStub->setIsBundledPaymentsEnabled(true);

        $payments = $this->getPlannedPayments();
        $this->processor->process($payments);

        $bundledPaymentIds = [];
        foreach ($this->getPlannedPayments() as $plannedPayment) {
            $parentId = $plannedPayment->getParentId();
            if ($parentId && !in_array($parentId, $bundledPaymentIds)) {
                $bundledPaymentIds[] = $parentId;
            }

            $this->assertEquals(PaymentInterface::STATUS_UNPROCESSABLE, $plannedPayment->getPaymentStatus());
        }

        $this->assertCount(1, $bundledPaymentIds);

        $actualPayments = $this->getActualPayments();
        $this->assertCount(3, $actualPayments);
        foreach ($actualPayments as $actualPayment) {
            $this->assertEquals(PaymentInterface::TYPE_ACTUAL, $actualPayment->getType());
            $this->assertEquals(PaymentInterface::STATUS_PENDING, $actualPayment->getPaymentStatus());
            $this->assertEquals('2018-05-18', $actualPayment->getScheduledAt());

            $profile = $actualPayment->getProfile();
            $this->assertEquals(['token_id' => $profile->getPaymentTokenId()], $actualPayment->getPaymentData());

            $paymentId = $actualPayment->getId();
            if (in_array($paymentId, $bundledPaymentIds)) {
                $this->assertEquals(null, $actualPayment->getPaymentPeriod());
                $this->assertEquals(8.00, $actualPayment->getTotalScheduled());
                $this->assertEquals(16.00, $actualPayment->getBaseTotalScheduled());
            } else {
                $incrementId = $profile->getIncrementId();
                if ($incrementId == '000000001') {
                    $this->assertEquals(PaymentInterface::PERIOD_REGULAR, $actualPayment->getPaymentPeriod());
                    $this->assertEquals(5.00, $actualPayment->getTotalScheduled());
                    $this->assertEquals(10.00, $actualPayment->getBaseTotalScheduled());
                }
                if ($incrementId == '000000002') {
                    $this->assertEquals(PaymentInterface::PERIOD_TRIAL, $actualPayment->getPaymentPeriod());
                    $this->assertEquals(3.00, $actualPayment->getTotalScheduled());
                    $this->assertEquals(6.00, $actualPayment->getBaseTotalScheduled());
                }
            }
        }
    }

    public function testProcessBundlePaymentsDisabled()
    {
        $this->configStub->setIsBundledPaymentsEnabled(false);

        $payments = $this->getPlannedPayments();
        $this->processor->process($payments);

        foreach ($this->getPlannedPayments() as $plannedPayment) {
            $this->assertNull($plannedPayment->getParentId());
            $this->assertEquals(PaymentInterface::STATUS_UNPROCESSABLE, $plannedPayment->getPaymentStatus());
        }

        $actualPayments = $this->getActualPayments();
        $this->assertCount(4, $actualPayments);
        foreach ($actualPayments as $actualPayment) {
            $this->assertEquals(PaymentInterface::TYPE_ACTUAL, $actualPayment->getType());
            $this->assertEquals(PaymentInterface::STATUS_PENDING, $actualPayment->getPaymentStatus());
            $this->assertEquals('2018-05-18', $actualPayment->getScheduledAt());

            $profile = $actualPayment->getProfile();
            $this->assertEquals(
                ['token_id' => $profile->getPaymentTokenId()],
                $actualPayment->getPaymentData()
            );

            $incrementId = $profile->getIncrementId();
            if ($incrementId == '000000001') {
                $this->assertEquals(PaymentInterface::PERIOD_REGULAR, $actualPayment->getPaymentPeriod());
                $this->assertEquals(5.00, $actualPayment->getTotalScheduled());
                $this->assertEquals(10.00, $actualPayment->getBaseTotalScheduled());
            }
            if ($incrementId == '000000002') {
                $this->assertEquals(PaymentInterface::PERIOD_TRIAL, $actualPayment->getPaymentPeriod());
                $this->assertEquals(3.00, $actualPayment->getTotalScheduled());
                $this->assertEquals(6.00, $actualPayment->getBaseTotalScheduled());
            }
            if ($incrementId == '000000003') {
                $this->assertEquals(PaymentInterface::PERIOD_REGULAR, $actualPayment->getPaymentPeriod());
                $this->assertEquals(6.00, $actualPayment->getTotalScheduled());
                $this->assertEquals(12.00, $actualPayment->getBaseTotalScheduled());
            }
            if ($incrementId == '000000004') {
                $this->assertEquals(PaymentInterface::PERIOD_TRIAL, $actualPayment->getPaymentPeriod());
                $this->assertEquals(2.00, $actualPayment->getTotalScheduled());
                $this->assertEquals(4.00, $actualPayment->getBaseTotalScheduled());
            }
        }
    }

    /**
     * Get planned payments
     *
     * @return PaymentInterface[]
     */
    private function getPlannedPayments()
    {
        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addFieldToFilter('type', ['eq' => PaymentInterface::TYPE_PLANNED])
            ->addFieldToFilter('scheduled_at', ['lteq' => '2018-05-18']);
        return $collection->getItems();
    }

    /**
     * Get actual payments
     *
     * @return PaymentInterface[]
     */
    private function getActualPayments()
    {
        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addFieldToFilter('type', ['eq' => PaymentInterface::TYPE_ACTUAL])
            ->addFieldToFilter('scheduled_at', ['lteq' => '2018-05-18']);
        return $collection->getItems();
    }
}
