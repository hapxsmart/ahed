<?php
namespace Aheadworks\Sarp2\Model\Email\Template;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class ObjectsInstantiation
 * @package Aheadworks\Sarp2\Model\Email\Template
 */
class ObjectsInstantiation
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Instantiate objects of template variables data
     *
     * @param $data
     * @return mixed
     */
    public function instantiate($data)
    {
        if (isset($data['profileId'])) {
            $data['profile'] = $this->profileRepository->get($data['profileId']);
        }
        if (isset($data['orderId']) && $data['orderId']) {
            $data['order'] = $this->orderRepository->get($data['orderId']);
        }
        return $data;
    }
}
