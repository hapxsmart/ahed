<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity;

use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatterInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule as ScheduleModel;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Schedule
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity
 */
class Schedule implements DataFormatterInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var array
     */
    private $fieldsToLog = [
        'schedule_id',
        'period',
        'frequency',
        'is_initial_paid',
        'trial_count',
        'trial_total_count',
        'regular_count',
        'regular_total_count',
    ];

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function format($subject)
    {
        if ($subject instanceof ScheduleInterface) {
            /** @var ScheduleModel $subject */
            $data = array_intersect_key($subject->getData(), array_flip($this->fieldsToLog));
            return $this->serializer->serialize($data);
        }
        return '';
    }
}
