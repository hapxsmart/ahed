<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class Index
 */
class Index extends AbstractProfile
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $profile = $this->getProfile();
            $resultPage = $this->resultPageFactory->create();
            $planDefinition = $profile->getPlanDefinition();
            $pageTitle = $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                ? __('Payment Plan Profile #%1', $profile->getIncrementId())
                : __('Subscription Profile #%1', $profile->getIncrementId());
            $resultPage->getConfig()->getTitle()->set($pageTitle);

            $this
                ->registerProfile()
                ->setUrlToBackLink($resultPage)
                ->setTitleToBackLink($resultPage, __('All Subscriptions'))
                ->setActionClassToBackLink($resultPage, 'secondary');
            if ($this->getRequest()->getParam(ProfileInterface::HASH)) {
                $resultPage->getLayout()->unsetElement('customer.account.link.back');
            }

            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRefererUrl()
    {
        return $this->_url->getUrl('aw_sarp2/profile/');
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isEditActionAvailable($profileId);
    }
}
