<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Copier as PlanCopier;
use Aheadworks\Sarp2\Model\Plan\PostDataProcessor\Composite as PostDataProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Error;
use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Class Save
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Plan
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::plans';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanCopier
     */
    private $planCopier;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     * @param PlanRepositoryInterface $planRepository
     * @param PlanInterfaceFactory $planFactory
     * @param PlanCopier $planCopier
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor,
        PlanRepositoryInterface $planRepository,
        PlanInterfaceFactory $planFactory,
        PlanCopier $planCopier,
        DataObjectHelper $dataObjectHelper
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->postDataProcessor = $postDataProcessor;
        $this->planRepository = $planRepository;
        $this->planFactory = $planFactory;
        $this->planCopier = $planCopier;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($requestData) {
            $entityData = $this->postDataProcessor->prepareEntityData($requestData);
            try {
                $plan = $this->performSave($entityData);

                $this->dataPersistor->clear('aw_sarp2_plan');
                $this->messageManager->addSuccessMessage(__('The plan was successfully saved.'));

                $back = $this->getRequest()->getParam('back', false);
                if ($back == 'edit') {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'plan_id' => $plan->getPlanId(),
                            '_current' => true
                        ]
                    );
                } elseif($back == 'duplicate') {
                    $duplicate = $this->planCopier->copy($plan);
                    $this->messageManager->addSuccessMessage(__('The plan was successfully duplicated.'));
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'plan_id' => $duplicate->getPlanId(),
                            '_current' => true,
                            'back' => null
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/');
            } catch (ValidatorException $exception) {
                $this->addValidationMessages($exception);
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the plan.')
                );
            }

            $this->dataPersistor->set('aw_sarp2_plan', $entityData);

            if (isset($entityData['plan_id'])) {
                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'plan_id' => $entityData['plan_id'],
                        '_current' => true
                    ]
                );
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return PlanInterface
     */
    private function performSave($data)
    {
        $planId = isset($data['plan_id']) ? $data['plan_id'] : false;
        $plan = $planId
            ? $this->planRepository->get($planId)
            : $this->planFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $plan,
            $data,
            PlanInterface::class
        );
        return $this->planRepository->save($plan);
    }

    /**
     * Add validator exceptions message to message collection
     *
     * @param ValidatorException $exception
     * @return void
     */
    private function addValidationMessages(ValidatorException $exception)
    {
        $messages = $exception->getMessages();
        if (empty($messages)) {
            $messages = [$exception->getMessage()];
        }
        foreach ($messages as $message) {
            if (!$message instanceof Error) {
                $message = new Error($message);
            }
            $this->messageManager->addMessage($message);
        }
    }
}
