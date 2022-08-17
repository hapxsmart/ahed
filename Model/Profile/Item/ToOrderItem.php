<?php
namespace Aheadworks\Sarp2\Model\Profile\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor\BundleOptionPriceProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Tax\Model\Config as TaxConfig;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Recalculation\OrderItem as OrderItemRecalculation;

class ToOrderItem
{
    /**
     * @var OrderItemInterfaceFactory
     */
    private $orderItemFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var BundleOptionPriceProcessor
     */
    private $bundleOrderOptionsProcessor;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderItemRecalculation
     */
    private $orderItemRecalculation;

    /**
     * @var array
     */
    private $selfCopyMapExcludeTax = [
        [OrderItemInterface::PRICE, OrderItemInterface::ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE, OrderItemInterface::BASE_ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE, OrderItemInterface::BASE_COST]
    ];

    /**
     * @var array
     */
    private $selfCopyMapIncludeTax = [
        [OrderItemInterface::PRICE_INCL_TAX, OrderItemInterface::ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE_INCL_TAX, OrderItemInterface::BASE_ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE_INCL_TAX, OrderItemInterface::BASE_COST]
    ];

    /**
     * @param OrderItemInterfaceFactory $orderItemFactory
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TaxConfig $taxConfig
     * @param BundleOptionPriceProcessor $bundleOrderOptionsProcessor
     * @param Config $config
     * @param OrderItemRecalculation $orderItemRecalculation
     */
    public function __construct(
        OrderItemInterfaceFactory $orderItemFactory,
        Copy $objectCopyService,
        CopySelf $selfCopyService,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TaxConfig $taxConfig,
        BundleOptionPriceProcessor $bundleOrderOptionsProcessor,
        Config $config,
        OrderItemRecalculation $orderItemRecalculation
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->taxConfig = $taxConfig;
        $this->bundleOrderOptionsProcessor = $bundleOrderOptionsProcessor;
        $this->config = $config;
        $this->orderItemRecalculation = $orderItemRecalculation;
    }

    /**
     * Convert profile item to order item
     *
     * @param ProfileItemInterface $profileItem
     * @param string $paymentPeriod
     * @param array $data
     * @return OrderItemInterface
     * @throws LocalizedException
     */
    public function convert(ProfileItemInterface $profileItem, $paymentPeriod, $data = [])
    {
        $profileItemClone = clone $profileItem;
        $options = $profileItemClone->getProductOptions();
        if (is_array($options)) {
            $options['aw_sarp2_subscription_payment_period'] = $paymentPeriod;
        } else {
            $options->setData('aw_sarp2_subscription_payment_period', $paymentPeriod);
        }
        $options = $this->bundleOrderOptionsProcessor->process($options);

        $this->dataObjectHelper->populateWithArray(
            $profileItemClone,
            $this->dataObjectProcessor->buildOutputDataArray($profileItemClone, ProfileItemInterface::class),
            ProfileItemInterface::class
        );
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'aw_sarp2_convert_profile_item',
            'to_order_item',
            $profileItemClone
        );
        $orderItemData = array_merge(
            $orderItemData,
            $this->objectCopyService->getDataFromFieldset(
                'aw_sarp2_convert_profile_item',
                'to_order_item_' . $paymentPeriod,
                $profileItemClone
            )
        );
        $storeId = $profileItemClone->getStoreId();

        if ($this->config->isRecalculationOfTotalsEnabled($storeId) && isset($options['aw_sarp2_subscription_plan'])) {
            $orderItemData = $this->orderItemRecalculation->recalculateTotals($orderItemData, $profileItemClone);
        }

        $isPriceIncludesTax = $this->taxConfig->priceIncludesTax($storeId);
        $orderItemData = $isPriceIncludesTax
            ? $this->selfCopyService->copyByMap($orderItemData, $this->selfCopyMapIncludeTax)
            : $this->selfCopyService->copyByMap($orderItemData, $this->selfCopyMapExcludeTax);

        if (!empty($data)) {
            $orderItemData = array_merge($orderItemData, $data);
        }

        /** @var OrderItemInterface|Item $orderItem */
        $orderItem = $this->orderItemFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderItem,
            $orderItemData,
            OrderItemInterface::class
        );
        $orderItem->setProductOptions($options);

        return $orderItem;
    }
}
