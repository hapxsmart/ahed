<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Payment;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class PaymentsListTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Payment
 */
class PaymentsListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PaymentsList
     */
    private $paymentList;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->paymentList = $this->objectManager->create(PaymentsList::class);
        $this->profileResource = $this->objectManager->create(ProfileResource::class);
    }


    public function testHasForProfileExist()
    {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

        $this->assertTrue($this->paymentList->hasForProfile($profile->getProfileId()));
    }


    public function testHasForProfileNotExist()
    {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

        $this->assertFalse($this->paymentList->hasForProfile($profile->getProfileId()));
    }
}
