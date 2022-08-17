<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;

/**
 * Class Plan
 */
class Plan extends AbstractProfile
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

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
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set($this->getTitle());

            $this
                ->registerProfile()
                ->setUrlToBackLink($resultPage);
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

        /**
     * Get title
     *
     * @return Phrase
     * @throws NotFoundException
     */
    private function getTitle()
    {
        $planDefinition = $this->getProfile()->getPlanDefinition();
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Change Installment')
            : __('Change Subscription');
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isEditPlanActionAvailable($profileId);
    }
}
