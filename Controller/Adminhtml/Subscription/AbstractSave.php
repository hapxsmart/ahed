<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Data\OperationInterface as DataOperationInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractSave extends Action
{
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var DataOperationInterface
     */
    private $dataOperation;

    /**
     * @param Context $context
     * @param DataOperationInterface $dataOperation
     */
    public function __construct(
        Context $context,
        DataOperationInterface $dataOperation
    ) {
        parent::__construct($context);
        $this->dataOperation = $dataOperation;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam(ProfileInterface::PROFILE_ID);
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($profileId) {
            try {
                $this->dataOperation->execute($profileId, $data);
                return $resultRedirect->setPath(
                    '*/subscription/view/',
                    [ProfileInterface::PROFILE_ID => $profileId]
                );
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while renew the subscription.')
                );
            }
        }

        return $resultRedirect->setPath(
            '*/subscription_edit/*',
            [ProfileInterface::PROFILE_ID => $profileId, '_current' => true]
        );
    }
}
