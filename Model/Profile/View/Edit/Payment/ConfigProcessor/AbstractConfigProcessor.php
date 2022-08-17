<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProcessor;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class AbstractConfigProcessor
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProcessor
 */
abstract class AbstractConfigProcessor implements ConfigProcessorInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Set new value if exists
     *
     * @param array $config
     * @param string $path
     * @param mixed $value
     * @return array
     */
    protected function setValue(&$config, $path, $value)
    {
        $component = $this->arrayManager->get($path, $config);
        if ($component) {
            $config = $this->arrayManager->set(
                $path,
                $config,
                $value
            );
        }

        return $config;
    }
}
