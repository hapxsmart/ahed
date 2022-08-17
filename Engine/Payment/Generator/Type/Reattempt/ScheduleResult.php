<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt;

/**
 * Class ScheduleResult
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt
 */
class ScheduleResult
{
    /**
     * Reattempt types
     */
    const REATTEMPT_TYPE_RETRY = 'retry';
    const REATTEMPT_TYPE_NEXT = 'next';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $date;

    /**
     * @param string $type
     * @param string $date
     */
    public function __construct($type, $date)
    {
        $this->type = $type;
        $this->date = $date;
    }

    /**
     * Get reattempt type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get reattempt date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }
}
