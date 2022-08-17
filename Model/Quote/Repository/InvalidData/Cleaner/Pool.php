<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\CleanerInterface;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect\ResultInterface;

/**
 * Class Pool
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner
 */
class Pool
{
    /**
     * @var CleanerInterface
     */
    private $instances = [];

    /**
     * @var array
     */
    private $cleaners = [
        CouponCode::class => [
            ResultInterface::REASON_COUPON_ON_SUBSCRIPTION_CART,
            ResultInterface::REASON_COUPON_ON_MIXED_CART
        ],
        GiftCards::class => [
            ResultInterface::REASON_EE_GIFT_CARD_ON_SUBSCRIPTION_CART,
            ResultInterface::REASON_EE_GIFT_CARD_ON_MIXED_CART
        ],
        AwGiftCards::class => [
            ResultInterface::REASON_AW_GIFT_CARD_ON_SUBSCRIPTION_CART,
            ResultInterface::REASON_AW_GIFT_CARD_ON_MIXED_CART
        ]
    ];

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     * @param array $cleaners
     */
    public function __construct(
        Factory $factory,
        array $cleaners = []
    ) {
        $this->factory = $factory;
        $this->cleaners = array_merge($this->cleaners, $cleaners);
    }

    /**
     * Get invalid data cleaner instance
     *
     * @param string $reason
     * @return CleanerInterface
     * @throws \Exception
     */
    public function getCleaner($reason)
    {
        if (!isset($this->instances[$reason])) {
            $found = false;
            foreach ($this->cleaners as $className => $reasons) {
                if (in_array($reason, $reasons)) {
                    $this->instances[$reason] = $this->factory->create($className);
                    $found = true;
                }
            }
            if (!$found) {
                throw new \InvalidArgumentException(
                    sprintf('Unknown invalid quote data cleaner: %s requested', $reason)
                );
            }
        }
        return $this->instances[$reason];
    }
}
