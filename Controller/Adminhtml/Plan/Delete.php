<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Plan
 */
class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::plans';

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var ProfileFinder
     */
    private $profileFinder;

    /**
     * @param Context $context
     * @param PlanRepositoryInterface $planRepository
     * @param ProfileFinder $profileFinder
     */
    public function __construct(
        Context $context,
        PlanRepositoryInterface $planRepository,
        ProfileFinder $profileFinder
    ) {
        parent::__construct($context);
        $this->planRepository = $planRepository;
        $this->profileFinder = $profileFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $planId = (int)$this->getRequest()->getParam('plan_id');
        if ($planId) {
            try {
                $profiles = $this->profileFinder->getActualProfilesByPlanId($planId);
                if (empty($profiles)) {
                    $this->planRepository->deleteById($planId);
                    $this->messageManager->addSuccessMessage(__('Plan was successfully deleted.'));
                    return $resultRedirect->setPath('*/*/');
                } else {
                    $this->messageManager->addErrorMessage(__(
                        'Can’t delete this plan because it is used in one or more subscription profiles.'
                        . ' Please disable it instead.'
                    ));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Plan could not be deleted.'));
        return $resultRedirect->setPath('*/*/');
    }
}
