<?php
namespace Aheadworks\Sarp2\Model\Profile\Item\Options;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;

/**
 * Class Extractor
 */
class Extractor
{
    /**
     * @var SubscriptionOptionInterfaceFactory
     */
    private $subscriptionOptionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * Extractor constructor.
     *
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SubscriptionOptionInterfaceFactory $subscriptionOptionFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SubscriptionOptionInterfaceFactory $subscriptionOptionFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->optionRepository = $optionRepository;
        $this->subscriptionOptionFactory = $subscriptionOptionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Retrieve item subscription option
     *
     * @param ProfileItemInterface|Item $item
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionOptionFromItem($item)
    {
        $option = null;
        $productOptions = $item->getProductOptions();
        if (isset($productOptions['info_buyRequest']['aw_sarp2_subscription_option'])) {
            $option = $this->subscriptionOptionFactory->create();
            $optionArray = $productOptions['info_buyRequest']['aw_sarp2_subscription_option'];
            $this->dataObjectHelper->populateWithArray($option, $optionArray, SubscriptionOptionInterface::class);
        } elseif (isset($productOptions['aw_sarp2_subscription_option']['option_id'])) {
            $optionId = $productOptions['aw_sarp2_subscription_option']['option_id'];
            if ($optionId) {
                $option = $this->optionRepository->get($optionId);
            }
        } else {
            $optionId = isset($productOptions['info_buyRequest']['aw_sarp2_subscription_type'])
                ? $productOptions['info_buyRequest']['aw_sarp2_subscription_type']
                : null;
            if ($optionId) {
                $option = $this->optionRepository->get($optionId);
            } else {
                return null;
            }
        }

        return $option;
    }

    /**
     * Get option id from buyRequest
     *
     * @param DataObject $buyRequest
     * @return int|null
     */
    public function getSubscriptionOptionIdFromBuyRequest($buyRequest)
    {
        if ($buyRequest->getData('aw_sarp2_subscription_type') != null) {
            return (int)$buyRequest->getData('aw_sarp2_subscription_type');
        } elseif (isset($buyRequest->getData('options')['aw_sarp2_subscription_type']) &&
            $buyRequest->getData('options')['aw_sarp2_subscription_type'] != null
        ) {
            return (int)$buyRequest->getData('options')['aw_sarp2_subscription_type'];
        }

        return null;
    }
}
