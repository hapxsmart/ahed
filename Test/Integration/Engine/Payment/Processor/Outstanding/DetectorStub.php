<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Payment\Processor\Outstanding;

use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\DetectResult;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class DetectorStub
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Payment\Processor\Outstanding
 */
class DetectorStub extends Detector
{
    /**
     * {@inheritdoc}
     */
    public function detect($payments)
    {
        return Bootstrap::getObjectManager()->create(
            DetectResult::class,
            [
                'todayPayments' => $payments,
                'outstandingPayments' => []
            ]
        );
    }
}
