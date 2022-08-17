<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class MassCancel
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Subscription
 */
class MassCancel extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var ActionPermission
     */
    private $actionPermission;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProfileManagementInterface $profileManagement
     * @param ActionPermission $actionPermission
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProfileManagementInterface $profileManagement,
        ActionPermission $actionPermission
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->profileManagement = $profileManagement;
        $this->actionPermission = $actionPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $successCount = 0;
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            foreach ($collection->getAllIds() as $profileId) {
                $allowedStatuses = $this->profileManagement->getAllowedStatuses($profileId);
                if (in_array(Status::CANCELLED, $allowedStatuses)) {
                    try {
                        if ($this->isCancelActionAllowed($profileId)) {
                            $this->profileManagement->changeStatusAction($profileId, Status::CANCELLED);
                            $successCount++;
                        }
                    } catch (\Exception $exception) {
                        throw $exception;
                    }
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been cancelled.', $successCount)
            );
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while cancel the items.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Is cancel action allowed
     *
     * @param int $profileId
     * @return bool
     * @throws LocalizedException
     */
    private function isCancelActionAllowed($profileId)
    {
        return $this->actionPermission->isCancelActionAvailableForAdmin($profileId)
            && $this->actionPermission->isCancelActionAvailableOnPeriodHolder($profileId);
    }
}
