<?php
namespace Aheadworks\Sarp2\Model\DateTime;

use Magento\Framework\Stdlib\DateTime;

/**
 * Class Modifier
 * @package Aheadworks\Sarp2\Model\DateTime
 */
class Modifier
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param DateTime $dateTime
     */
    public function __construct(
        DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * Copy time
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function copyTime($source, $destination)
    {
        $sourceDateTime = new \DateTime($source ?? 'now');
        $destinationDateTime = new \DateTime($destination ?? 'now');

        $timeArray = explode(':', $sourceDateTime->format('H:i:s'));
        $destinationDateTime->setTime($timeArray[0], $timeArray[1], $timeArray[2]);

        return $this->dateTime->formatDate($destinationDateTime);
    }
}
