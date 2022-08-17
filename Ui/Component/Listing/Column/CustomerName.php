<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

use Aheadworks\Sarp2\Model\Customer\Checker as CustomerChecker;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class CustomerName
 * @package Aheadworks\Sarp2\Ui\Component\Listing\Column
 */
class CustomerName extends Link
{
    /**
     * @var CustomerChecker
     */
    private $customerChecker;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param CustomerChecker $customerChecker
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        CustomerChecker $customerChecker,
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
        $this->customerChecker = $customerChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function isLink(array $item)
    {
        return $this->customerChecker->isRegisteredCustomer(
            $item['customer_id'] ?? null
        );
    }
}
