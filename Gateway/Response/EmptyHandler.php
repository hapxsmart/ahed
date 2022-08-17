<?php
namespace Aheadworks\Sarp2\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class EmptyHandler
 *
 * @package Aheadworks\Sarp2\Gateway\Response
 */
class EmptyHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
    }
}
