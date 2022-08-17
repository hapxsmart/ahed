<?php
namespace Aheadworks\Sarp2\Model\Profile\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Sarp2\Model\Profile\Source
 */
class Status implements OptionSourceInterface
{
    /**
     * 'Pending' status
     */
    const PENDING = 'pending';

    /**
     * 'Active' status
     */
    const ACTIVE = 'active';

    /**
     * 'Suspended' status
     */
    const SUSPENDED = 'suspended';

    /**
     * 'Cancelled' status
     */
    const CANCELLED = 'cancelled';

    /**
     * 'Expired' status
     */
    const EXPIRED = 'expired';

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::PENDING,
                    'label' => __('Pending')
                ],
                [
                    'value' => self::ACTIVE,
                    'label' => __('Active')
                ],
                [
                    'value' => self::SUSPENDED,
                    'label' => __('Suspended')
                ],
                [
                    'value' => self::CANCELLED,
                    'label' => __('Cancelled')
                ],
                [
                    'value' => self::EXPIRED,
                    'label' => __('Expired')
                ]
            ];
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
