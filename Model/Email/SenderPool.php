<?php
namespace Aheadworks\Sarp2\Model\Email;

/**
 * Class SenderPool
 * @package Aheadworks\Sarp2\Model\Email
 */
class SenderPool
{
    /**
     * @var array
     */
    private $senders = [];

    /**
     * @var SenderInterface[]
     */
    private $senderInstances = [];

    /**
     * @var SenderFactory
     */
    private $senderFactory;

    /**
     * @param SenderFactory $senderFactory
     * @param array $senders
     */
    public function __construct(
        SenderFactory $senderFactory,
        array $senders = []
    ) {
        $this->senderFactory = $senderFactory;
        $this->senders = array_merge($this->senders, $senders);
    }

    /**
     * Get email sender of specified event type
     *
     * @param string $eventType
     * @return SenderInterface
     * @throws \Exception
     */
    public function getSender($eventType)
    {
        if (!isset($this->senderInstances[$eventType])) {
            if (!isset($this->senders[$eventType])) {
                throw new \Exception(sprintf('Unknown email sender: %s requested', $eventType));
            }
            $this->senderInstances[$eventType] = $this->senderFactory->create($this->senders[$eventType]);
        }
        return $this->senderInstances[$eventType];
    }
}
