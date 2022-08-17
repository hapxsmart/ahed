<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Configurable;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Generic\InstallmentsMode as GenericInstallmentsMode;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;

class InstallmentsMode extends GenericInstallmentsMode
{
    /**
     * @var ChildProcessor
     */
    private $childProcessor;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param PlanRepositoryInterface $planRepository
     * @param ChildProcessor $childProcessor
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionsRepository,
        PlanRepositoryInterface $planRepository,
        ChildProcessor $childProcessor
    ) {
        parent::__construct($optionsRepository, $planRepository);
        $this->childProcessor = $childProcessor;
    }

    /**
     * Get installments mode config
     *
     * @param ProductInterface $product
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface|null $profile
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfig($product, $item = null, $profile = null)
    {
        $installmentsModeInfo = [];
        $childProducts = $this->childProcessor->getAllowedList($product);

        foreach ($childProducts as $childProduct) {
            $subscriptionOptions = $this->childProcessor->getSubscriptionOptions($childProduct, $product->getId());
            foreach ($subscriptionOptions as $option) {
                $installmentsModeInfo[$option->getOptionId()][$childProduct->getId()] = $this->getInstallmentModeInfo($option);
            }
        }

        return $installmentsModeInfo;
    }
}
