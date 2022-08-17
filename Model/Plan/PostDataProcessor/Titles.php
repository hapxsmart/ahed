<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class Titles
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class Titles implements ProcessorInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (isset($data['titles'])) {
            foreach ($data['titles'] as $index => $titleData) {
                $isRemoved = isset($titleData['removed'])
                    ? $this->booleanUtils->toBoolean($titleData['removed'])
                    : true;

                if ($isRemoved) {
                    unset($data['titles'][$index]);
                }
            }
        }

        return $data;
    }
}
