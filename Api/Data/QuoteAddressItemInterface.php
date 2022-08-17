<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface QuoteAddressItemInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface QuoteAddressItemInterface extends ExtensibleDataInterface
{

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\QuoteAddressItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\QuoteAddressItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\QuoteAddressItemExtensionInterface $extensionAttributes
    );
}
