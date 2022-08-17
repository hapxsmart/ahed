<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Customer;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Ui\Component\Form\Fieldset;

class SubscriptionsFieldset extends Fieldset implements ComponentVisibilityInterface
{
    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;
        parent::__construct($context, $components, $data);
    }

    /**
     * Render component in case customer is modified and hide for new customer
     *
     * @return boolean
     */
    public function isComponentVisible(): bool
    {
        $customerId = $this->context->getRequestParam('id');
        return (bool)$customerId;
    }
}
