<?php
namespace Aheadworks\Sarp2\ViewModel\Customer\Subscription\Edit\Item;

use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class UpdateButton
 *
 * @package Aheadworks\Sarp2\ViewModel\Customer\Subscription\Edit\Item
 */
class UpdateButton implements ArgumentInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Check if allow one-time editing product item
     *
     * @return int
     */
    public function canOneTimeEditing()
    {
        return (int)$this->config->canOneTimeEditProductItem();
    }
}
