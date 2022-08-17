<?php
namespace Aheadworks\Sarp2\Model\Product\Checker;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type\Generic as GenericHandler;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type\HandlerFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class IsSubscription
 * @package Aheadworks\Sarp2\Model\Product\Checker
 */
class IsSubscription
{
    /**
     * @var GenericHandler
     */
    private $genericHandler;

    /**
     * @var HandlerFactory
     */
    private $handlerFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $typeHandlers = [
        /**Configurable::TYPE_CODE => ConfigurableHandler::class**/
    ];

    /**
     * @var array
     */
    private $checkCache = [];

    /**
     * @param GenericHandler $genericHandler
     * @param HandlerFactory $handlerFactory
     * @param ProductRepositoryInterface $productRepository
     * @param array $typeHandlers
     */
    public function __construct(
        GenericHandler $genericHandler,
        HandlerFactory $handlerFactory,
        ProductRepositoryInterface $productRepository,
        $typeHandlers = []
    ) {
        $this->genericHandler = $genericHandler;
        $this->handlerFactory = $handlerFactory;
        $this->productRepository = $productRepository;
        $this->typeHandlers = array_merge($this->typeHandlers, $typeHandlers);
    }

    /**
     * Check if subscribe action available for product
     *
     * @param ProductInterface $product
     * @param bool $subscriptionOnly
     * @return bool
     */
    public function check($product, $subscriptionOnly = false)
    {
        $key = $product->getId() . '-' . ($subscriptionOnly ? '1' : '0');
        if (!isset($this->checkCache[$key])) {
            $typeId = $product->getTypeId();
            $handler = $this->getHandler($typeId);
            $this->checkCache[$key] = $handler->check($product, $subscriptionOnly);
        }
        return $this->checkCache[$key];
    }

    /**
     * Check if subscribe action available for product with specified Id
     *
     * @param int $productId
     * @param bool $subscriptionOnly
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkById($productId, $subscriptionOnly = false)
    {
        return $this->check(
            $this->productRepository->getById($productId),
            $subscriptionOnly
        );
    }

    /**
     * Retrieve type handler
     *
     * @param string $type
     * @return IsSubscription\Type\HandlerInterface
     */
    private function getHandler($type)
    {
        if (isset($this->typeHandlers[$type])) {
            if (is_string($this->typeHandlers[$type])) {
                $this->typeHandlers[$type] = $this->handlerFactory->create($this->typeHandlers[$type]);
            }
            return $this->typeHandlers[$type];
        } else {
            return $this->genericHandler;
        }
    }
}
