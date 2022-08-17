<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect\ResultInterface;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\AbstractValidator;
use Magento\Framework\Module\Manager;

/**
 * Class AwGiftCards
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Reason\Validator
 */
class AwGiftCards extends AbstractValidator
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * {@inheritdoc}
     */
    protected $errorMessage = 'Gift Card(s) can not be applied to the cart which contains subscription(s)';

    /**
     * @param HasSubscriptions $quoteChecker
     * @param Manager $moduleManager
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        Manager $moduleManager
    ) {
        parent::__construct($quoteChecker);
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($quote)
    {
        $this->reset();
        $extension = $quote->getExtensionAttributes();
        if ($this->moduleManager->isEnabled('Aheadworks_Giftcard')
            && $extension
            && $extension->getAwGiftcardCodes()
        ) {
            $this->isValid = false;
            $this->reason = $this->quoteChecker->checkHasBoth($quote)
                ? ResultInterface::REASON_AW_GIFT_CARD_ON_MIXED_CART
                : ResultInterface::REASON_AW_GIFT_CARD_ON_SUBSCRIPTION_CART;
        }
        return $this->isValid;
    }
}
