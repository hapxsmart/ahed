<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\AddItemsFromQuoteToNearest\Validator;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class NextPaymentStatus
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\AddItemsFromQuoteToNearest\Validator
 */
class NextPaymentStatus extends AbstractValidator
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        ProfileManagementInterface $profileManagement
    ) {
        $this->profileManagement = $profileManagement;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    protected function performValidation($profile, $action)
    {
        $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());

        if ($nextPaymentInfo->getPaymentStatus() == ScheduledPaymentInfoInterface::PAYMENT_STATUS_LAST_PERIOD_HOLDER) {
            $this->addMessages(['This action is not allowed for membership subscription.']);
        }
    }
}
