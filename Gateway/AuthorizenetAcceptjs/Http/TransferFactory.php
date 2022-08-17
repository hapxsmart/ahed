<?php
namespace Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\FilterInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransferFactory
 *
 * @package Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var array
     */
    private $payloadFilters;

    /**
     * @param TransferBuilder $transferBuilder
     * @param FilterInterface[] $payloadFilters
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        array $payloadFilters = []
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->payloadFilters = $payloadFilters;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        foreach ($this->payloadFilters as $filter) {
            $request = $filter->filter($request);
        }

        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
