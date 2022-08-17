<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\StatusApplierPool;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var StatusApplierPool
     */
    private $statusApplierPool;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @param ResultFactory $validationResultFactory
     * @param StatusApplierPool $statusApplierPool
     * @param ValidatorComposite $validator
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        StatusApplierPool $statusApplierPool,
        ValidatorComposite $validator
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->statusApplierPool = $statusApplierPool;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $status = $action->getData()->getStatus();

        $statusApplier = $this->statusApplierPool->getApplier($status);
        $statusApplier->apply($profile, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $isValid = $this->validator->isValid($profile, $action);

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $this->validator->getMessage();
        }
        return $this->validationResultFactory->create($resultData);
    }
}
