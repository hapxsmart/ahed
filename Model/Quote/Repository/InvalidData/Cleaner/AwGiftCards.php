<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\CleanerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager;

/**
 * Class AwGiftCards
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner
 */
class AwGiftCards implements CleanerInterface
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * {@inheritdoc}
     */
    public function clean($quote)
    {
        $extension = $quote->getExtensionAttributes();
        if ($this->moduleManager->isEnabled('Aheadworks_Giftcard')
            && $extension
            && $extension->getAwGiftcardCodes()
        ) {
            /** @var DataObject $giftCardCode */
            foreach ($extension->getAwGiftcardCodes() as $giftCardCode) {
                if ($giftCardCode instanceof DataObject) {
                    $giftCardCode->setIsRemove(true);
                }
            }
            $quote->setExtensionAttributes($extension);
        }
        return $quote;
    }
}
