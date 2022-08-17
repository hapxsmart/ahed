<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Profile;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Profile\Scheduler;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\PrePaymentInfo;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SchedulerTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Profile
 */
class SchedulerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Scheduler
     */
    private $scheduler;

    /***
     * @var ProfileResource
     */
    private $profileResource;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scheduler = $this->objectManager->create(Scheduler::class);
        $this->profileResource = $this->objectManager->create(ProfileResource::class);
    }

    public function testScheduleInitialAndTrialPaid()
    {
        /** @var PrePaymentInfo $prePaymentInfo */
        $prePaymentInfo = $this->objectManager->create(PrePaymentInfo::class);
        $prePaymentInfo->setIsInitialFeePaid(true)
            ->setIsTrialPaid(true)
            ->setIsRegularPaid(false);

        $profiles = $this->getProfiles([1, 2, 3], $prePaymentInfo);
        $this->scheduler->schedule($profiles);

        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        /** @var Payment[] $payments */
        $payments = array_values($collection->getItems());
        $this->assertCount(3, $payments);
    }

    public function testScheduleBundledInitialAndTrialPaid()
    {
        /** @var PrePaymentInfo $prePaymentInfo */
        $prePaymentInfo = $this->objectManager->create(PrePaymentInfo::class);
        $prePaymentInfo->setIsInitialFeePaid(true)
            ->setIsTrialPaid(true)
            ->setIsRegularPaid(false);

        $profiles = $this->getProfiles([4, 5, 6], $prePaymentInfo);
        $this->scheduler->schedule($profiles);

        /** @var Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        /** @var Payment[] $payments */
        $payments = array_values($collection->getItems());
        $this->assertCount(3, $payments);
    }

    /**
     * Get profiles
     *
     * @param array $profileIds
     * @param PrePaymentInfo $prePaymentInfo
     * @return Profile[]
     */
    private function getProfiles($profileIds, $prePaymentInfo)
    {
        $profiles = [];
        foreach ($profileIds as $profileId) {
            /** @var Profile $profile */
            $profile = $this->objectManager->create(Profile::class);
            $this->profileResource->load($profile, $profileId);
            $profile->setPrePaymentInfo($prePaymentInfo);
            $profiles[] = $profile;
        }
        return $profiles;
    }
}
