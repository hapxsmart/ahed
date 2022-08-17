<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Item\Options\Extractor as OptionExtractor;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Magento\Framework\DataObject\Factory;

class InitialFee implements CollectorInterface
{
    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var OptionExtractor
     */
    private $optionExtractor;

    /**
     * @param GroupInterface $totalsGroup
     * @param Factory $dataObjectFactory
     * @param OptionExtractor $subscriptionOptionExtractor
     */
    public function __construct(
        GroupInterface $totalsGroup,
        Factory $dataObjectFactory,
        OptionExtractor $subscriptionOptionExtractor
    ) {
        $this->totalsGroup = $totalsGroup;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->optionExtractor = $subscriptionOptionExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileInterface $profile)
    {
        $baseFeeTotal = 0;
        $currencyOptionConvert = PopulatorInterface::CURRENCY_OPTION_CONVERT;
        $profileCurrencyCode = $profile->getProfileCurrencyCode();

        if ($profile->getPlanDefinition()->getIsInitialFeeEnabled()) {
            foreach ($profile->getItems() as $item) {
                if (!$item->getParentItem()) {
                    $option = $this->optionExtractor->getSubscriptionOptionFromItem($item);
                    if ($option) {
                        $baseFee = $option->getInitialFee();
                        $this->totalsGroup->getPopulator(ProfileItemInterface::class)
                            ->populate(
                                $item,
                                $this->dataObjectFactory->create(['fee' => $baseFee]),
                                $currencyOptionConvert,
                                $profileCurrencyCode
                            );

                        $baseFeeTotal += $baseFee * $item->getQty();
                    }
                }
            }

            $this->totalsGroup->getPopulator(ProfileInterface::class)
                ->populate(
                    $profile,
                    $this->dataObjectFactory->create(['fee' => $baseFeeTotal]),
                    $currencyOptionConvert,
                    $profileCurrencyCode
                );
        }
    }
}
