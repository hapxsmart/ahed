<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter;

use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatterInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create log data formatter instance
     *
     * @param string $className
     * @return DataFormatterInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof DataFormatterInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . DataFormatterInterface::class
            );
        }
        return $instance;
    }
}
