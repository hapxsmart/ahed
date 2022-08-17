<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Address\ToQuoteAddress;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ToQuote
 *
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ToQuote
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ToQuoteAddress
     */
    private $profileAddressToQuoteAddress;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param QuoteFactory $quoteFactory
     * @param ToQuoteAddress $profileAddressToQuoteAddress
     * @param Copy $objectCopyService
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        ToQuoteAddress $profileAddressToQuoteAddress,
        Copy $objectCopyService
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->profileAddressToQuoteAddress = $profileAddressToQuoteAddress;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert profile to quote
     *
     * @param ProfileInterface $profile
     * @return Quote
     */
    public function convert(ProfileInterface $profile)
    {
        $quote = $this->quoteFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_quote',
            'to_quote',
            $profile,
            $quote
        );
        $quote
            ->setIsActive(false)
            ->setCustomerIsGuest(0)
            ->setIsVirtual($profile->getIsVirtual())
            ->setBaseGrandTotal($profile->getBaseRegularGrandTotal())
            ->setGrandTotal($profile->getRegularGrandTotal());

        $quoteBillingAddress = $this->profileAddressToQuoteAddress->convert($profile->getBillingAddress());
        $quote->setBillingAddress($quoteBillingAddress);

        if (!$profile->getIsVirtual()) {
            $quoteShippingAddress = $this->profileAddressToQuoteAddress->convert($profile->getShippingAddress());
            $quote->setShippingAddress($quoteShippingAddress);
        }

        return $quote;
    }
}
