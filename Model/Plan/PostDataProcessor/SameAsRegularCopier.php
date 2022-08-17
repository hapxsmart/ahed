<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Aheadworks\Sarp2\Model\Plan\DataProvider\SameAsRegularMap;

/**
 * Class SameAsRegularCopier
 *
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class SameAsRegularCopier implements ProcessorInterface
{
    /**
     * @var SameAsRegularMap
     */
    private $map;

    /**
     * @param SameAsRegularMap $map
     */
    public function __construct(SameAsRegularMap $map)
    {
        $this->map = $map;
    }

    /**
     * @inheritdoc
     */
    public function prepareEntityData($data)
    {
        if (isset($data['trial_same_as_regular']) && (bool)$data['trial_same_as_regular']) {
            foreach ($this->map->get() as $samePair) {
                $data['definition'][$samePair['to']] = $data['definition'][$samePair['from']];
            }
        }

        return $data;
    }
}
