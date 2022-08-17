<?php
namespace Aheadworks\Sarp2\Controller\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class Item extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ProfileItemRepositoryInterface
     */
    private $profileItemRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileItemRepositoryInterface $profileItemRepository
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileItemRepositoryInterface $profileItemRepository,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        $this->profileItemRepository = $profileItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $itemId = (int)$this->getRequest()->getParam('item_id');

        try {
            $item = $this->profileItemRepository->get($itemId);
            $this->dataPersistor->set('aw_sarp2_profile_item', $item->getData());
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Configure Product and Quantity'));

            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
