<?php
namespace Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs;

use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;

/**
 * Class ProfileAdapter
 * @package Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs
 */
class ProfileAdapter
{
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @param CommandPoolInterface $commandPool
     */
    public function __construct(
        CommandPoolInterface $commandPool
    ) {
        $this->commandPool = $commandPool;
    }

    /**
     * Execute profile command
     *
     * @param $commandCode
     * @param array $arguments
     * @return ResultInterface|null
     * @throws NotFoundException
     * @throws CommandException
     */
    public function execute($commandCode, array $arguments = [])
    {
        return $this->commandPool->get($commandCode)->execute($arguments);
    }
}
