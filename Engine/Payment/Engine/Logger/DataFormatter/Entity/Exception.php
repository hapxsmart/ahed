<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity;

use Aheadworks\Sarp2\Engine\Exception\ScheduledPaymentException;
use Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatterInterface;

/**
 * Class Exception
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger\DataFormatter\Entity
 */
class Exception implements DataFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($subject)
    {
        if ($subject instanceof \Exception) {
            /** @var \Exception $subject */
            return $subject instanceof ScheduledPaymentException
                ? $subject->getMessage()
                : sprintf(
                    '"%s" has been raised with message \'%s\'',
                    get_class($subject),
                    $subject->getMessage()
                );
        }
        return '';
    }
}
