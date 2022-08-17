<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Total\Group;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Group\Factory;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group\Initial;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group\Regular;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group\Trial;

/**
 * Class Resolver
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Total\Group
 */
class Resolver
{
    /**
     * @var Factory
     */
    private $groupFactory;

    /**
     * @var array
     */
    private $groups = [
        PaymentInterface::PERIOD_INITIAL => Initial::class,
        PaymentInterface::PERIOD_TRIAL => Trial::class,
        PaymentInterface::PERIOD_REGULAR => Regular::class
    ];

    /**
     * @var GroupInterface[]
     */
    private $groupInstances = [];

    /**
     * @param Factory $groupFactory
     * @param array $groups
     */
    public function __construct(
        Factory $groupFactory,
        array $groups = []
    ) {
        $this->groupFactory = $groupFactory;
        $this->groups = array_merge($this->groups, $groups);
    }

    /**
     * Get totals group of specified
     *
     * @param $paymentPeriod
     * @return GroupInterface
     */
    public function getTotalsGroup($paymentPeriod)
    {
        if (!isset($this->groupInstances[$paymentPeriod])) {
            if (!isset($this->groups[$paymentPeriod])) {
                throw new \InvalidArgumentException(
                    'Unknown ' . $paymentPeriod . ' payment period type.'
                );
            }
            $this->groupInstances[$paymentPeriod] = $this->groupFactory->create(
                $this->groups[$paymentPeriod]
            );
        }
        return $this->groupInstances[$paymentPeriod];
    }
}
