<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Handler\AfterPlaceOrder;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Handler\HandlerInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile\ItemManagement;

/**
 * Class DeleteOneOffItems
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Handler\AfterPlaceOrder
 */
class DeleteOneOffItems implements HandlerInterface
{
    /**
     * @var ItemManagement
     */
    private $itemManagement;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param ItemManagement $itemManagement
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        ItemManagement $itemManagement,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->itemManagement = $itemManagement;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Process payment
     *
     * @param PaymentInterface $payment
     * @return void
     */
    public function handle(PaymentInterface $payment)
    {
        return;
        try {
            $profile = $this->profileRepository->get($payment->getProfileId());
            $this->itemManagement->deleteOneOffItems($profile);
            //TODO: disable profile validation when saving profile.
            //      Improved solution needs to be found.
            $profile->setOrigData(ProfileInterface::STATUS, $profile->getStatus());
            $this->profileRepository->save($profile);
        } catch (\Exception $exception) {
        }
    }
}
