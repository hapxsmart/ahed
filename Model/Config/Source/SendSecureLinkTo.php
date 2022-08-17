<?php
namespace Aheadworks\Sarp2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SendSecureLinkTo implements OptionSourceInterface
{
    const DISABLE = 0;
    const GUEST_CUSTOMERS = 1;
    const ALL_CUSTOMERS = 2;

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLE, 'label' => __('Disable')],
            ['value' => self::GUEST_CUSTOMERS, 'label' => __('Guest Customers (Default)')],
            ['value' => self::ALL_CUSTOMERS, 'label' => __('All Customers')]
        ];
    }
}
