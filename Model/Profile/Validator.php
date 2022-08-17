<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\CompositeDetector;
use Aheadworks\Sarp2\Helper\Validator\EmptyValidator;
use Aheadworks\Sarp2\Model\Payment\Checker\OfflinePayment;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Profile
 */
class Validator extends AbstractValidator
{
    /**
     * @var CompositeDetector
     */
    private $profileActionDetector;

    /**
     * @var ApplierPool
     */
    private $actionApplierPool;

    /**
     * @var OfflinePayment
     */
    private $offlinePaymentChecker;

    /**
     * @var EmptyValidator
     */
    private $emptyValidator;

    /**
     * @var array string[]
     */
    private $skipEmptyPaymentTokenValidation = [];

    /**
     * @param CompositeDetector $profileActionDetector
     * @param ApplierPool $actionApplierPool
     * @param OfflinePayment $offlinePaymentChecker
     * @param EmptyValidator $emptyValidator
     * @param array $skipEmptyPaymentTokenValidation
     */
    public function __construct(
        CompositeDetector $profileActionDetector,
        ApplierPool $actionApplierPool,
        OfflinePayment $offlinePaymentChecker,
        EmptyValidator $emptyValidator,
        array $skipEmptyPaymentTokenValidation = []
    ) {
        $this->profileActionDetector = $profileActionDetector;
        $this->actionApplierPool = $actionApplierPool;
        $this->offlinePaymentChecker = $offlinePaymentChecker;
        $this->emptyValidator = $emptyValidator;
        $this->skipEmptyPaymentTokenValidation = array_merge(
            $this->skipEmptyPaymentTokenValidation,
            $skipEmptyPaymentTokenValidation
        );
    }

    /**
     * Returns true if and only if profile entity meets the validation requirements
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isValid($profile)
    {
        $this->_clearMessages();

        if ($this->emptyValidator->isValid($profile->getStoreId())) {
            $this->_addMessages(['Store Id is required.']);
        }
        if ($this->emptyValidator->isValid($profile->getPlanDefinitionId())) {
            $this->_addMessages(['Plan definition Id is required.']);
        }
        if ($this->emptyValidator->isValid($profile->getStartDate())) {
            $this->_addMessages(['Start date is required.']);
        }
        if ($this->emptyValidator->isValid($profile->getPaymentTokenId())
            && !in_array($profile->getPaymentMethod(), $this->skipEmptyPaymentTokenValidation)
            && !$this->offlinePaymentChecker->check($profile->getPaymentMethod())
        ) {
            $this->_addMessages(['Payment token Id is required.']);
        }

        $action = $this->profileActionDetector->detect($profile);
        if ($action) {
            $validationResult = $this->actionApplierPool->getApplier($action->getType())
                ->validate($profile, $action);
            if (!$validationResult->isValid()) {
                $this->_addMessages(['Profile action is not available: ' . $validationResult->getMessage()]);
            }
        }

        return empty($this->getMessages());
    }
}
