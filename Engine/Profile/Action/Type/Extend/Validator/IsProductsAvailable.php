<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator;

use Aheadworks\Sarp2\Engine\Profile\Checker\ProductsAvailable;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class IsProductsAvailable
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator
 */
class IsProductsAvailable extends AbstractValidator
{
    /**
     * @var ProductsAvailable
     */
    private $productsAvailableChecker;

    /**
     * @param ProductsAvailable $productsAvailableChecker
     */
    public function __construct(ProductsAvailable $productsAvailableChecker)
    {
        $this->productsAvailableChecker = $productsAvailableChecker;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        if (!$this->productsAvailableChecker->check($profile)) {
            $this->addMessages(['The Extend action is not possible for this subscription.']);
        }
    }
}
