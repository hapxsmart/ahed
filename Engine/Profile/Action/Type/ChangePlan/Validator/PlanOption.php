<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\Validator;

use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\OptionResolver;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class PlanOption
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\Validator
 */
class PlanOption extends AbstractValidator
{
    /**
     * @var OptionResolver
     */
    private $optionResolver;

    /**
     * @param OptionResolver $optionResolver
     */
    public function __construct(
        OptionResolver $optionResolver
    ) {
        $this->optionResolver = $optionResolver;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function performValidation($profile, $action)
    {
        $newPlanId = $action->getData()->getNewPlanId();
        if ($profile->getPlanId() !== $newPlanId) {
            foreach ($profile->getItems() as &$item) {
                if ($item->getParentItemId()) {
                    continue;
                }

                $resolveResponse = $this->optionResolver->resolveOptionForItem($item, $newPlanId);
                if ($resolveResponse && !$resolveResponse->getOption()) {
                    $this->addMessages(['Selected plan is not available in product.']);
                    break;
                }
            }
        } else {
            $this->addMessages(['Selected plan is used in subscription.']);
        }
    }
}
