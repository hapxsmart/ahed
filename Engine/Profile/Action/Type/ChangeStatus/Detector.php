<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionFactory;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\DetectorInterface;
use Aheadworks\Sarp2\Model\Profile;

/**
 * Class Detector
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus
 */
class Detector implements DetectorInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @param ActionFactory $actionFactory
     */
    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function detect(ProfileInterface $profile)
    {
        $status = $profile->getStatus();
        /** @var Profile $profile */
        if ($profile->getOrigData(ProfileInterface::STATUS) != $status) {
            return $this->actionFactory->create(
                [
                    'type' => ActionInterface::ACTION_TYPE_CHANGE_STATUS,
                    'data' => ['status' => $status]
                ]
            );
        }
        return null;
    }
}
