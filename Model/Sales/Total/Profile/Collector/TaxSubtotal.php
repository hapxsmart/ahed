<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Tax\QuoteDetails\Builder;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\Keyer;
use Magento\Framework\DataObject\Factory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

/**
 * Class TaxSubtotal
 */
class TaxSubtotal implements CollectorInterface
{
    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @var Builder
     */
    private $quoteDetailsBuilder;

    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var Keyer
     */
    private $keyer;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param TaxCalculationInterface $taxCalculation
     * @param Config $taxConfig
     * @param Builder $quoteDetailsBuilder
     * @param GroupInterface $totalsGroup
     * @param Factory $dataObjectFactory
     * @param Keyer $keyer
     * @param Summator $grandSummator
     */
    public function __construct(
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig,
        Builder $quoteDetailsBuilder,
        GroupInterface $totalsGroup,
        Factory $dataObjectFactory,
        Keyer $keyer,
        Summator $grandSummator
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->totalsGroup = $totalsGroup;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->keyer = $keyer;
        $this->grandSummator = $grandSummator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileInterface $profile)
    {
        $items = $profile->getItems();
        if ($items) {
            $storeId = $profile->getStoreId();
            $isPriceIncludesTax = $this->taxConfig->priceIncludesTax($storeId);

            $quoteDetails = $this->quoteDetailsBuilder->setItemType('product')
                ->setProfile($profile)
                ->setTotalsProvider($this->totalsGroup->getProvider())
                ->setIsBaseCurrency(false)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->build();
            $taxDetails = $this->taxCalculation->calculateTax($quoteDetails, $storeId);

            $baseQuoteDetails = $this->quoteDetailsBuilder->setItemType('product')
                ->setProfile($profile)
                ->setTotalsProvider($this->totalsGroup->getProvider())
                ->setIsBaseCurrency(true)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->build();
            $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails, $storeId);

            $this->processTaxDetails($taxDetails, $profile, false)
                ->processTaxDetails($baseTaxDetails, $profile, true);
        }
    }

    /**
     * Process tax details
     *
     * @param TaxDetailsInterface $taxDetails
     * @param ProfileInterface $profile
     * @param bool $isBaseCurrency
     * @return $this
     */
    private function processTaxDetails($taxDetails, $profile, $isBaseCurrency)
    {
        $subtotal = 0;
        $tax = 0;
        $subtotalInclTax = 0;

        $profileCurrency = $profile->getProfileCurrencyCode();
        $currencyOption = $isBaseCurrency
            ? PopulatorInterface::CURRENCY_OPTION_USE_BASE
            : PopulatorInterface::CURRENCY_OPTION_USE_STORE;

        /** @var Item $keyedItems */
        $keyedItems = $this->keyer->keyBy($profile->getItems(), 'getTaxCalculationItemId');
        foreach ($taxDetails->getItems() as $item) {
            $profileItem = $keyedItems[$item->getCode()];
            if ($item->getType() == 'product') {
                $this->totalsGroup->getPopulator(ProfileItemInterface::class)
                    ->populate(
                        $profileItem,
                        $this->dataObjectFactory->create(
                            [
                                'price' => $item->getPrice(),
                                'price_incl_tax' => $item->getPriceInclTax(),
                                'row_total' => $item->getRowTotal(),
                                'row_total_incl_tax' => $item->getRowTotalInclTax(),
                                'row_tax' => $item->getRowTax(),
                                'tax_percent' => $item->getTaxPercent()
                            ]
                        ),
                        $currencyOption,
                        $profileCurrency
                    );

                if (!$profileItem->getParentItem()) {
                    $subtotal += $item->getRowTotal();
                    $tax += $item->getRowTax();
                    $subtotalInclTax += $item->getRowTotalInclTax();
                }
            }
        }
        $this->totalsGroup->getPopulator(ProfileInterface::class)
            ->populate(
                $profile,
                $this->dataObjectFactory->create(
                    [
                        'subtotal' => $subtotal,
                        'subtotal_incl_tax' => $subtotalInclTax,
                        'tax' => $tax
                    ]
                ),
                $currencyOption,
                $profileCurrency
            );
        if ($isBaseCurrency) {
            $this->grandSummator->setAmount(
                $this->totalsGroup->getCode() . '_subtotal',
                $subtotalInclTax
            );
        }

        return $this;
    }
}
