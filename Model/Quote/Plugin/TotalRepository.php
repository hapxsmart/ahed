<?php
namespace Aheadworks\Sarp2\Model\Quote\Plugin;

use Aheadworks\Sarp2\Model\Quote\Total\Modifier;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Class TotalRepository
 * @package Aheadworks\Sarp2\Model\Quote\Plugin
 */
class TotalRepository
{
    /**
     * @var Modifier
     */
    private $modifier;

    /**
     * @param Modifier $modifier
     */
    public function __construct(Modifier $modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param TotalsInterface $totals
     * @param int $cartId
     * @return TotalsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CartTotalRepositoryInterface $subject, TotalsInterface $totals, $cartId)
    {
        return $this->modifier->modify($totals, $cartId);
    }
}
