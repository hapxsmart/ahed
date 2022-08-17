<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderId
 * @package Aheadworks\Sarp2\Ui\Component\Listing\Column
 */
class OrderId extends Link
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $url,
            $components,
            $data
        );
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareLinkText(array $item)
    {
        $orderId = $item[$this->getName()];
        $order = $this->orderRepository->get($orderId);
        return '#' . $order->getIncrementId();
    }
}
