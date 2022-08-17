<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Checker;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription as ProductChecker;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription\CheckerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class IsSubscription implements CheckerInterface
{
    /**
     * @var ProductChecker
     */
    private $productChecker;

    /**
     * @param ProductChecker $productChecker
     */
    public function __construct(ProductChecker $productChecker)
    {
        $this->productChecker = $productChecker;
    }

    /**
     * @inheritDoc
     * @param QuoteItem $item
     */
    public function check($item)
    {
        $product = $item->getParentItem() ? $item->getParentItem()->getProduct() : $item->getProduct();
        if ($this->productChecker->check($product)) {
            $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
            return $optionId && $optionId->getValue();
        }
        return false;
    }
}
