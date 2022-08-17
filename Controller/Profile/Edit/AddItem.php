<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\UrlBuilder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AddItem
 */
class AddItem extends Action
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProfileManagementInterface $profileManagement
     * @param StoreManagerInterface $storeManager
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ProfileManagementInterface $profileManagement,
        StoreManagerInterface $storeManager,
        UrlBuilder $urlBuilder
    ) {
        parent::__construct($context);
        $this->profileManagement = $profileManagement;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $customerId = $this->customerSession->getCustomerId();
            $storeId = $this->storeManager->getStore()->getId();
            $this->profileManagement->addItemsFromQuoteToNearestProfile($customerId, $storeId);
            $url = $this->_url->getUrl(
                'aw_sarp2/profile/index',
                $this->urlBuilder->getParams(null, $this->getRequest())
            );
            $this->messageManager->addComplexSuccessMessage(
                'awSarp2NearestProfileUpdateSuccessMessage',
                ['url' => $url]
            );
        } catch (\Exception $e) {
            $this->messageManager->addWarningMessage(__('We can\'t do it right now.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        return $this->resultRedirectFactory->create()->setPath('/');
    }
}
