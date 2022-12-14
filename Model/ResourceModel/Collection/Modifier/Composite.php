<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Collection\Modifier;

use Aheadworks\Sarp2\Model\ResourceModel\Collection\ModifierInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;

class Composite implements ModifierInterface
{
    /**
     * @var ModifierInterface[]
     */
    private $modifierList;

    /**
     * @param ModifierInterface[] $modifierList
     */
    public function __construct(
        array $modifierList = []
    ) {
        $this->modifierList = $modifierList;
    }

    /**
     * @inheritDoc
     */
    public function modifyData($item)
    {
        foreach ($this->modifierList as $modifier) {
            if (!$modifier instanceof ModifierInterface) {
                throw new ConfigurationMismatchException(
                    __('Collection item modifier must implement %1', ModifierInterface::class)
                );
            }
            $item = $modifier->modifyData($item);
        }
        return $item;
    }
}
