<?php
namespace Aheadworks\Sarp2\Ui\Component\Listing;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns as UiColumns;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Columns
 * @package Aheadworks\Sarp2\Ui\Component\Listing
 */
class Columns extends UiColumns
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ContextInterface $context
     * @param RequestInterface $request
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->request = $request;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');
        foreach ($config['fieldSwitcher'] as $rule) {
            if ($this->request->getParam('display') == $rule['display']) {
                foreach ($this->getChildComponents() as &$component) {
                    if (in_array($component->getName(), $rule['action']['columns'])) {
                        $componentConfig = $component->getData('config');
                        $componentConfig[$rule['action']['name']] = $rule['action']['value'];
                        $component->setData('config', $componentConfig);
                    }
                }
            }
        }
    }
}
