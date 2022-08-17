<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Plan
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::plans';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param PlanInterfaceFactory $planFactory
     * @param PlanRepositoryInterface $planRepository
     * @param Registry $coreRegistry
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PlanInterfaceFactory $planFactory,
        PlanRepositoryInterface $planRepository,
        Registry $coreRegistry,
        DataObjectProcessor $dataObjectProcessor,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->planFactory = $planFactory;
        $this->planRepository = $planRepository;
        $this->coreRegistry = $coreRegistry;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $planId = (int)$this->getRequest()->getParam('plan_id');
        /** @var PlanInterface $plan */
        $plan = $this->planFactory->create();
        if ($planId) {
            try {
                $plan = $this->planRepository->get($planId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(
                    __('This plan doesn\'t exist.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while editing the plan.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }

        $this->registerPlanTitlesData($plan);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Sarp2::plans')
            ->getConfig()->getTitle()->prepend(
                $planId ?  __('Edit Plan') : __('New Plan')
            );
        return $resultPage;
    }

    /**
     * Register plan titles data
     *
     * @param PlanInterface $plan
     * @return void
     */
    private function registerPlanTitlesData(PlanInterface $plan)
    {
        $planData = $this->dataPersistor->get('aw_sarp2_plan')
            ? $this->dataPersistor->get('aw_sarp2_plan')
            : $this->dataObjectProcessor->buildOutputDataArray($plan, PlanInterface::class);
        $planTitlesData = isset($planData['titles'])
            ? $planData['titles']
            : [];
        $this->coreRegistry->register('aw_srp2_plan_titles', $planTitlesData);
    }
}
