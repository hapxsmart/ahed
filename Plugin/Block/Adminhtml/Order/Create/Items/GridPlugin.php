<?php
namespace Aheadworks\Sarp2\Plugin\Block\Adminhtml\Order\Create\Items;

use Aheadworks\Sarp2\Block\Adminhtml\Order\Create\Grid\Items\SubscriptionDetailsBlock;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions\Provider\Decorator\PlanNameDecorator as OptionProvider;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid;

/**
 * Class GridPlugin
 *
 * @package Aheadworks\Sarp2\Plugin\Block\Adminhtml\Order\Create\Items
 */
class GridPlugin
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var OptionProvider
     */
    private $optionProvider;

    /**
     * @var SubscriptionDetailsBlock
     */
    private $detailsBlock;

    /**
     * GridPlugin constructor.
     *
     * @param IsSubscription $isSubscriptionChecker
     * @param OptionProvider $optionProvider
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        OptionProvider $optionProvider
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionProvider = $optionProvider;
    }

    /**
     * @param Grid $subject
     * @param bool $result
     * @param Item $item
     * @return bool
     */
    public function afterCanApplyCustomPrice(Grid $subject, $result, $item)
    {
        if ($result && $this->isSubscriptionChecker->check($item)) {
            return false;
        }

        return $result;
    }

    /**
     * @param Grid $subject
     * @param $result
     * @param $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetConfigureButtonHtml(Grid $subject, $result, $item)
    {
        if ($this->isSubscriptionChecker->check($item)) {
            $details = $this->optionProvider->getSubscriptionOptions($item);
            $block = $this->getDetailsBlock($subject);
            $block->setSubscriptionDetails($details);

            return $block->toHtml() . $result;
        }

        return $result;
    }

    /**
     * Create Subscription Details Block
     *
     * @param Grid $gridBlock
     * @return SubscriptionDetailsBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDetailsBlock(Grid $gridBlock)
    {
        if (!$this->detailsBlock) {
            $this->detailsBlock = $gridBlock->getLayout()->createBlock(
                SubscriptionDetailsBlock::class
            );
        }

        return $this->detailsBlock;
    }
}
