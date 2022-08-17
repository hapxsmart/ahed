<?php
namespace Aheadworks\Sarp2\Model\Sales\Total;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface GroupInterface
 * @package Aheadworks\Sarp2\Model\Sales\Total
 */
interface GroupInterface
{
    /**#@+
     * Total group codes
     */
    const CODE_INITIAL = 'initial';
    const CODE_TRIAL = 'trial';
    const CODE_REGULAR = 'regular';
    /**#@-*/

    /**
     * Get total group code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get item price
     *
     * @param CartItemInterface|ProfileItemInterface $item
     * @param bool $useBaseCurrency
     * @return float
     */
    public function getItemPrice($item, $useBaseCurrency);

    /**
     * Get totals populator
     *
     * @param string $entityType
     * @return PopulatorInterface
     */
    public function getPopulator($entityType);

    /**
     * Get totals provider
     *
     * @return ProviderInterface
     */
    public function getProvider();
}
