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

/**
 * Class MassActivate
 * @package Aheadworks\Sarp2\Controller\Adminhtml\Subscription
 */
class MassActivate extends Action
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
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProfileManagementInterface $profileManagement
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->profileManagement = $profileManagement;
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
                if (in_array(Status::ACTIVE, $allowedStatuses)) {
                    try {
                        if ($this->profileManagement->isAllowedToReactivate($profileId)) {
                            $this->profileManagement->changeStatusAction($profileId, Status::ACTIVE);
                            $successCount++;
                        }
                    } catch (\Exception $exception) {
                        throw $exception;
                    }
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been activated.', $successCount)
            );
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while activate the items.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
