<?php
namespace Aheadworks\Sarp2\Model\Quote\Total;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemExtensionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Modifier
 * @package Aheadworks\Sarp2\Model\Quote\Total
 */
class Modifier
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var TotalsItemExtensionFactory
     */
    private $totalsItemExtensionFactory;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param IsSubscription $isSubscriptionChecker
     * @param TotalsItemExtensionFactory $totalsItemExtensionFactory
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        IsSubscription $isSubscriptionChecker,
        TotalsItemExtensionFactory $totalsItemExtensionFactory,
        SubscriptionOptionRepositoryInterface $optionRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->totalsItemExtensionFactory = $totalsItemExtensionFactory;
        $this->optionRepository = $optionRepository;
    }

    /**
     * Modify totals data according to new meanings of mixed cart totals
     *
     * @param TotalsInterface $totals
     * @param int $cartId
     * @return TotalsInterface
     */
    public function modify(TotalsInterface $totals, $cartId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        foreach ($totals->getItems() as $item) {
            $itemId = $item->getItemId();
            $quoteItem = $quote->getItemById($itemId);
            $this->modifyItem($item, $quoteItem);
        }
        return $totals;
    }

    /**
     * Modify totals item
     *
     * @param TotalsItemInterface $item
     * @param Item $quoteItem
     * @return TotalsItemInterface
     */
    private function modifyItem(&$item, $quoteItem)
    {
        $isSubscriptionItem = $this->isSubscriptionChecker->check($quoteItem);

        $totalsItemExtension = $item->getExtensionAttributes();
        if ($totalsItemExtension === null) {
            $totalsItemExtension = $this->totalsItemExtensionFactory->create();
        }

        $option = $quoteItem->getProduct()->getCustomOption('aw_sarp2_subscription_type');
        if ($option) {
            $totalsItemExtension->setData(
                'aw_sarp_frontend_displaying_mode',
                $this->getFrontendDisplayingMode($option->getValue())
            );
        }

        $totalsItemExtension->setAwSarpIsSubscription($isSubscriptionItem);
        $item->setExtensionAttributes($totalsItemExtension);

        return $item;
    }

    /**
     * Get frontend displaying mode by option id
     *
     * @param int $optionId
     * @return string
     * @throws LocalizedException
     */
    private function getFrontendDisplayingMode($optionId) {
        $subscriptionOption = $this->optionRepository->get($optionId);
        $planDefinition = $subscriptionOption->getPlan()->getDefinition();

        return $planDefinition->getFrontendDisplayingMode();
    }
}
