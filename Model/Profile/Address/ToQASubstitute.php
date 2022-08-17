<?php
namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\ToQuoteSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address as AddressSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\AddressFactory as AddressSubstituteFactory;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Framework\DataObject\Copy;

/**
 * Class ToQASubstitute
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class ToQASubstitute
{
    /**
     * @var AddressSubstituteFactory
     */
    private $addressSubstituteFactory;

    /**
     * @var ToQuoteSubstitute
     */
    private $toQuoteSubstitute;

    /**
     * @var ToQAItemSubstitute
     */
    private $toQAItemSubstitute;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @param AddressSubstituteFactory $addressSubstituteFactory
     * @param ToQuoteSubstitute $toQuoteSubstitute
     * @param ToQAItemSubstitute $toQAItemSubstitute
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     */
    public function __construct(
        AddressSubstituteFactory $addressSubstituteFactory,
        ToQuoteSubstitute $toQuoteSubstitute,
        ToQAItemSubstitute $toQAItemSubstitute,
        Copy $objectCopyService,
        CopySelf $selfCopyService
    ) {
        $this->addressSubstituteFactory = $addressSubstituteFactory;
        $this->toQuoteSubstitute = $toQuoteSubstitute;
        $this->toQAItemSubstitute = $toQAItemSubstitute;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
    }

    /**
     * Convert profile address into quote address substitute
     *
     * @param ProfileAddressInterface $profileAddress
     * @param string $totalsGroupCode
     * @param ProfileInterface $profile
     * @param ProfileItemInterface[]|null $profileItems
     * @param array $data
     * @return AddressSubstitute
     */
    public function convert(
        $profileAddress,
        $totalsGroupCode,
        $profile,
        $profileItems = null,
        array $data = []
    ) {
        /** @var AddressSubstitute $addressSubstitute */
        $addressSubstitute = $this->addressSubstituteFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_address',
            'to_quote_address_substitute',
            $profileAddress,
            $addressSubstitute
        );

        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile',
            'to_quote_address_substitute',
            $profile,
            $addressSubstitute
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile',
            'to_quote_address_substitute_' . $totalsGroupCode,
            $profile,
            $addressSubstitute
        );

        $this->selfCopyService->copyByMap(
            $addressSubstitute,
            [['base_subtotal', 'base_subtotal_with_discount']]
        );

        $quoteSubstitute = $this->toQuoteSubstitute->convert($profile, $totalsGroupCode);
        $allAddressItems = $this->toQAItemSubstitute->convertList(
            $profileItems ? : $profile->getItems(),
            $totalsGroupCode,
            ['address' => $addressSubstitute, 'quote' => $quoteSubstitute]
        );
        $addressSubstitute->addData(
            array_merge(
                [
                    'all_items' => $allAddressItems,
                    'quote' => $quoteSubstitute
                ],
                $data
            )
        );

        return $addressSubstitute;
    }
}
