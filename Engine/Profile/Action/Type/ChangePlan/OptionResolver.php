<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\OptionResolver\Response;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\OptionResolver\ResponseFactory;
use Aheadworks\Sarp2\Model\Profile\Item as ProfileItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

/**
 * Class OptionResolver
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\Applier
 */
class OptionResolver
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @param ResponseFactory $responseFactory
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     */
    public function __construct(
        ResponseFactory $responseFactory,
        SubscriptionOptionRepositoryInterface $optionsRepository
    ) {
        $this->responseFactory = $responseFactory;
        $this->optionsRepository = $optionsRepository;
    }

    /**
     * Retrieve option by plan
     *
     * @param ProfileItem|ProfileItemInterface $item
     * @param $newPlanId
     * @return Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resolveOptionForItem(ProfileItemInterface $item, $newPlanId)
    {
        /** @var Response $response */
        $response = $this->responseFactory->create();

        $productId = $item->getProductId();
        $option = $this->getOption($productId, $newPlanId);

        if (!$option) {
            return null;
        }

        $response
            ->setAwSarp2SubscriptionType($option->getOptionId())
            ->setOption($option);

        if ($item->getProductType() == ConfigurableProductType::TYPE_CODE) {
            $productId = $this->getChildProductId($item);
            $childItemOption = $this->getOption($productId, $newPlanId);
            if ($childItemOption) {
                $response->setOption($childItemOption);
            }
        }

        return $response;
    }

    /**
     * Retrieve child product id from profile item
     *
     * @param ProfileItem|ProfileItemInterface $item
     * @return int
     */
    private function getChildProductId($item)
    {
        foreach ($item->getChildItems() as $childItem) {
            return $childItem->getProductId();
        }

        return null;
    }

    /**
     * Retrieve option from repository
     *
     * @param int $productId
     * @param int $newPlanId
     * @return SubscriptionOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOption($productId, $newPlanId)
    {
        $options = $this->optionsRepository->getList($productId);
        foreach ($options as $option) {
            if ($newPlanId == $option->getPlanId()) {
                return $option;
            }
        }

        return null;
    }
}
