<?php
namespace Aheadworks\Sarp2\Model\DateTime;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class FormatConverter
 * @package Aheadworks\Sarp2\Model\DateTime
 */
class FormatConverter
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     */
    public function __construct(TimezoneInterface $localeDate)
    {
        $this->localeDate = $localeDate;
    }

    /**
     * Converts PHP IntlFormatter format to \DateTime format
     *
     * @param string $format
     * @return string
     */
    public function convertToDateTimeFormat($format = null)
    {
        $format = $format ? : $this->getDateFormat();
        $format = preg_replace('/d+/i', 'd', $format);
        $format = preg_replace('/m+/i', 'm', $format);
        $format = preg_replace('/y+/i', 'Y', $format);
        $format = preg_replace('/\s+\S+/', '', $format);

        return $format;
    }

    /**
     * Converts PHP IntlFormatter format to Js Calendar format
     *
     * @param string $format
     * @return string
     */
    public function convertToJsCalendarFormat($format = null)
    {
        $format = $format ? : $this->getDateFormat();
        $format = preg_replace('/d+/i', 'dd', $format);
        $format = preg_replace('/m+/i', 'mm', $format);
        $format = preg_replace('/y+/i', 'yyyy', $format);
        $format = preg_replace('/\s+\S+/', '', $format);

        return $format;
    }

    /**
     * Converts PHP IntlFormatter format to momemt Js format
     *
     * @param string $format
     * @return string
     */
    public function convertToMomentJsFormat($format = null)
    {
        $format = $format ? : $this->getDateFormat();
        $format = preg_replace('/d+/i', 'DD', $format);
        $format = preg_replace('/m+/i', 'MM', $format);
        $format = preg_replace('/y+/i', 'YYYY', $format);
        $format = preg_replace('/\s+\S+/', '', $format);

        return $format;
    }

    /**
     * Reformat datetime from specified format to 'Y-m-d H:i:s'
     *
     * @param string $dateTime
     * @param string $format
     * @return string
     */
    public function reformat(string $dateTime, string $format)
    {
        $reformatDate = \DateTime::createFromFormat(
            $format,
            $dateTime,
            new \DateTimeZone($this->localeDate->getConfigTimezone())
        );
        $reformatDate = $this->localeDate->date($reformatDate, null, false);

        return $reformatDate->format(DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Retrieve short date format
     *
     * @return string
     */
    private function getDateFormat()
    {
        return $this->localeDate->getDateFormat();
    }
}
