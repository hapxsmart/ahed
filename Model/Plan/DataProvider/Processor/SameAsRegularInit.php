<?php
namespace Aheadworks\Sarp2\Model\Plan\DataProvider\Processor;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Plan\DataProvider\SameAsRegularMap;

/**
 * Class SameAsRegularInit
 *
 * @package Aheadworks\Sarp2\Model\Plan\DataProvider\Processor
 */
class SameAsRegularInit implements ProcessorInterface
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
     * @inheritDoc
     */
    public function process($data)
    {
        if (!isset($data[PlanInterface::PLAN_ID])) {
            return $data;
        }

        $isSame = true;

        foreach ($this->map->get() as $samePair) {
            if ($data['definition'][$samePair['to']] != $data['definition'][$samePair['from']]) {
                $isSame = false;
            }
        }

        $data['trial_same_as_regular'] = (string)(int)$isSame;

        return $data;
    }
}
