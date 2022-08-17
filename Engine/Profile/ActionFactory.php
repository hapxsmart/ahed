<?php
namespace Aheadworks\Sarp2\Engine\Profile;

use Magento\Framework\DataObject\Factory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ActionFactory
 * @package Aheadworks\Sarp2\Engine\Profile
 */
class ActionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Factory $dataObjectFactory
    ) {
        $this->objectManager = $objectManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Create profile action instance
     *
     * @param array $data
     * @return Action
     */
    public function create(array $data)
    {
        $data['data'] = $this->dataObjectFactory->create(
            isset($data['data'])
                ? $data['data']
                : []
        );
        return $this->objectManager->create(Action::class, $data);
    }
}
