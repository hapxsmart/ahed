<?php
namespace Aheadworks\Sarp2\Model\Profile\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item as ItemSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\ItemFactory as ItemSubstituteFactory;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ToQAItemSubstitute
 * @package Aheadworks\Sarp2\Model\Profile\Item
 */
class ToQAItemSubstitute
{
    /**
     * @var ItemSubstituteFactory
     */
    private $substituteFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var AddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $itemIdCounter = 0;

    /**
     * @param ItemSubstituteFactory $substituteFactory
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     * @param ProductRepositoryInterface $productRepository
     * @param QuoteFactory $quoteFactory
     * @param AddressFactory $quoteAddressFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ItemSubstituteFactory $substituteFactory,
        Copy $objectCopyService,
        CopySelf $selfCopyService,
        ProductRepositoryInterface $productRepository,
        QuoteFactory $quoteFactory,
        AddressFactory $quoteAddressFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->substituteFactory = $substituteFactory;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Convert profile item into quote address item substitute
     *
     * @param ProfileItemInterface $profileItem
     * @param string $totalsGroupCode
     * @param array $data
     * @return ItemSubstitute
     */
    public function convert($profileItem, $totalsGroupCode, $data = [])
    {
        /** @var ItemSubstitute $substitute */
        $substitute = $this->substituteFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_item',
            'to_quote_address_item_substitute',
            $profileItem,
            $substitute
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_item',
            'to_quote_address_item_substitute_' . $totalsGroupCode,
            $profileItem,
            $substitute
        );
        $substitute
            ->setParentItemId(null)
            ->setQuoteAddressId(null)
            ->setQuoteItemId(null)
            ->setAppliedRuleIds('')
            ->setAdditionalData('')
            ->setDiscountAmount(0)
            ->setBaseDiscountAmount(0)
            ->setDiscountTaxCompensationAmount(0)
            ->setBaseDiscountTaxCompensationAmount(0)
            ->setSuperProductId(null)
            ->setParentProductId(null)
            ->setImage('')
            ->setDiscountPercent(0)
            ->setNoDiscount(0);

        $storeId = $profileItem->getStoreId();

        $substitute->addData(
            array_merge(
                [
                    'quote' => $this->quoteFactory->create(),
                    'address' => $this->quoteAddressFactory->create(),
                    'parent_item' => null,
                    'children' => [],
                    'store' => $this->storeManager->getStore($storeId),
                    'linked_profile_item' => $profileItem
                ],
                $data
            )
        );
        if ($substitute->getParentItem()) {
            $substitute->setParentItemId(++$this->itemIdCounter . '-substitute');
        }

        $product = $this->productRepository->getById($profileItem->getProductId(), false, $storeId);
        $shipmentType = $product->getShipmentType();
        $substitute->addData(
            [
                'product' => $product,
                'is_ship_separately' => $shipmentType && (int)$shipmentType == AbstractType::SHIPMENT_SEPARATELY
            ]
        );

        /** @var ItemSubstitute $substitute */
        $this->selfCopyService->copyByMap(
            $substitute,
            [
                ['price', 'calculation_price'],
                ['base_price', 'base_calculation_price'],
                ['price', 'cost'],
                ['base_price', 'base_cost'],
                ['price', 'calculation_price_original'],
                ['base_price', 'base_calculation_price_original'],
                ['price', 'original_price'],
                ['base_price', 'base_original_price'],
                ['price', 'converted_price'],
                ['row_total', 'row_total_with_discount'],
                ['base_row_total', 'base_row_total_with_discount']
            ]
        );

        return $substitute;
    }
}
