<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\SetPaymentToken\Validator;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class PaymentToken
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\SetPaymentToken\Validator
 */
class PaymentToken extends AbstractValidator
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        $tokenId = $action->getData()->getTokenId();

        try {
            $this->paymentTokenRepository->get($tokenId);
        } catch (LocalizedException $exception) {
            $this->addMessages(['Payment token is not isset.']);
        }
    }
}
