<?php
namespace Aheadworks\Sarp2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AttemptsCount
 *
 * @package Aheadworks\Sarp2\Model\Config\Source
 */
class AttemptsCount implements OptionSourceInterface
{
    /**
     * @var int
     */
    private $maxCount;

    /**
     * @param int $maxCount
     */
    public function __construct(
        $maxCount = 10
    ) {
        $this->maxCount = $maxCount;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];

        for ($i = 1; $i <= $this->maxCount; $i++) {
            $result[] = [
                'value' => $i,
                'label' => $i,
            ];
        }

        return $result;
    }
}
