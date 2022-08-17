<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\Applier;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class ArrayCopier
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\Applier
 */
class ArrayCopier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Copy node from source array to target array by path
     *
     * @param array $source
     * @param array $target
     * @param string|array $path
     * @return array
     */
    public function copyByPath(array $source, array $target, $path)
    {
        $paths = is_array($path) ? $path : [$path];

        foreach ($paths as $path) {
            $sourceValue = $this->arrayManager->get($path, $source);
            $targetValue = $this->arrayManager->get($path, $target);
            if ($sourceValue && !$targetValue) {
                $target = $this->arrayManager->set($path, $target, $sourceValue);
            }
        }

        return $target;
    }
}
