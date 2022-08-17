<?php
namespace Aheadworks\Sarp2\Model\Checkout\PaymentInfo;

use Aheadworks\Sarp2\Model\Payment\Sampler\Exception\ExceptionWithUnmaskedMessage;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * Class ExceptionHandler
 *
 * @package Aheadworks\Sarp2\Model\Checkout\PaymentInfo
 */
class ExceptionHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExceptionHandler constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle throwable exception
     *
     * @param \Exception $exception
     * @throws CouldNotSaveException
     */
    public function handle($exception)
    {
        $this->logger->critical($exception);

        $phrase = __('A server error stopped your order from being placed. Please try to place your order again.');
        if ($exception instanceof ExceptionWithUnmaskedMessage) {
            $phrase = __($exception->getMessage());
        } elseif ($exception->getPrevious() instanceof ExceptionWithUnmaskedMessage) {
            $phrase = __($exception->getPrevious()->getMessage());
        }

        throw new CouldNotSaveException(
            $phrase,
            $exception
        );
    }
}
