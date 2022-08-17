<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Plan
 */
class MassDelete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::plans';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

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
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param PlanRepositoryInterface $planRepository
     * @param ProfileFinder $profileFinder
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PlanRepositoryInterface $planRepository,
        ProfileFinder $profileFinder
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->planRepository = $planRepository;
        $this->profileFinder = $profileFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $deletedItems = 0;
            /** @var PlanInterface $plan */
            foreach ($collection->getItems() as $plan) {
                $planId = $plan->getId();
                $profiles = $this->profileFinder->getActualProfilesByPlanId($planId);
                if (empty($profiles)) {
                    $this->planRepository->deleteById($planId);
                    $deletedItems++;
                } else {
                    $this->messageManager->addErrorMessage(__(
                        'Can’t delete plan %1 because it is used in one or more subscription profiles.'
                        . ' Please disable it instead.', $plan->getName()
                    ));
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $deletedItems)
            );
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while deleting the items.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
