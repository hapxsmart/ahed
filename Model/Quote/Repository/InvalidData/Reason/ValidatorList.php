<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator\AwGiftCards;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator\Factory;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator\GiftCards;
use Magento\Quote\Model\Quote;

/**
 * Class ValidatorList
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason
 */
class ValidatorList
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var array
     */
    private $validators = [
        AwGiftCards::class,
        GiftCards::class
    ];

    /**
     * @var ValidatorInterface[]
     */
    private $list;

    /**
     * @param Factory $factory
     * @param array $validators
     */
    public function __construct(
        Factory $factory,
        array $validators = []
    ) {
        $this->factory = $factory;
        $this->validators = array_merge($this->validators, $validators);
    }

    /**
     * Get quote validators
     *
     * @return ValidatorInterface[]
     */
    public function getValidators()
    {
        if (!$this->list) {
            $this->list = [];
            foreach ($this->validators as $className) {
                $this->list[] = $this->factory->create($className);
            }
        }
        return $this->list;
    }
}
