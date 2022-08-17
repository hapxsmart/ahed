<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Handler\AfterPlaceOrder;

use Aheadworks\Sarp2\Engine\Payment\Processor\Handler\HandlerInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Class Composite
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Handler\AfterPlaceOrder
 */
class Composite implements HandlerInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $handlerList;

    /**
     * Composite constructor.
     *
     * @param HandlerInterface[] $handlerList
     */
    public function __construct(array $handlerList = [])
    {
        $this->handlerList = $handlerList;
    }

    /**
     * Process payment
     *
     * @param PaymentInterface $payment
     * @return void
     */
    public function handle(PaymentInterface $payment)
    {
        $profile = $payment->getProfile();
        foreach ($profile->getItems() as $profileItem) {
            $websiteId = $profile->getOrder()->getStore()->getWebsiteId();
            $profileItem->getProduct()
                ->setWebsiteId($websiteId)
                ->setCustomerGroupId($profile->getCustomerGroupId());
        }
        foreach ($this->handlerList as $handler) {
            $handler->handle($payment);
        }
    }
}
