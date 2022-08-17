<?php
namespace Aheadworks\Sarp2\Model\SalesRule\Rule\Condition\Product;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsInitial as IsInitialSubscriptionItemChecker;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription as IsSubscriptionItemChecker;

class IsRecalculationApplicable
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var IsInitialSubscriptionItemChecker
     */
    private $isInitialSubscriptionItemChecker;

    /**
     * @var IsSubscriptionItemChecker
     */
    private $isSubscriptionItemChecker;

    /**
     * @param Config $config
     * @param IsInitialSubscriptionItemChecker $isInitialSubscriptionItemChecker
     * @param IsSubscriptionItemChecker $isSubscriptionItemChecker
     */
    public function __construct(
        Config $config,
        IsInitialSubscriptionItemChecker $isInitialSubscriptionItemChecker,
        IsSubscriptionItemChecker $isSubscriptionItemChecker
    ) {
        $this->config = $config;
        $this->isInitialSubscriptionItemChecker = $isInitialSubscriptionItemChecker;
        $this->isSubscriptionItemChecker = $isSubscriptionItemChecker;
    }

    /**
     * @inheritDoc
     */
    public function check(AbstractModel $model)
    {
        // Free shipping in cart price rules must work regardless this option
        if (!$this->config->isRecalculationOfTotalsEnabled($model->getStoreId())) {
            return false;
        }

        if (!$model instanceof QuoteItem && !$model instanceof AddressItem) {
            return true;
        }

        // Recalculation is not needed for initial subscription item
        if ($this->isInitialSubscriptionItemChecker->check($model)) {
            return false;
        }

        // Recalculation is not needed for non subscription item
        if (!$this->isSubscriptionItemChecker->check($model)) {
            return false;
        }

        return true;
    }
}
