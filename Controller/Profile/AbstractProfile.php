<?php
namespace Aheadworks\Sarp2\Controller\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Controller\AbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\View\Result\Page;

/**
 * Class AbstractProfile
 */
abstract class AbstractProfile extends AbstractAction
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ActionPermission
     */
    protected $actionPermission;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param CustomerSession $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        CustomerSession $customerSession,
        Registry $registry,
        ActionPermission $actionPermission
    ) {
        parent::__construct($context, $profileRepository);
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->actionPermission = $actionPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        $hash = $this->_request->getParam(ProfileInterface::HASH);
        if (!$hash && !$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            return parent::dispatch($request);
        }
        if ($hash) {
            $this->getProfile();
        } elseif (!$this->isProfileBelongsToCustomer()) {
            throw new NotFoundException(__('Page not found.'));
        }

        if (!$this->isActionAllowed()) {
            $this->messageManager->addErrorMessage(__(
                'This action is not available.'
            ));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/profile/index');
        }

        $result = parent::dispatch($request);

        if ($hash && $result instanceof Page) {
            $result->getLayout()->unsetElement('sidebar.main');
            $result->getLayout()->unsetElement('sidebar.additional');
            $result->getConfig()->setRobots('NOINDEX,FOLLOW');
        }

        return $result;
    }

    /**
     * Register profile
     *
     * @return $this
     * @throws NotFoundException
     */
    protected function registerProfile()
    {
        $this->registry->register('profile', $this->getProfile());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRefererUrl()
    {
        return $this->_url->getUrl(
            '*/*/',
            $this->getParams($this->getProfile()->getProfileId())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getProfile()
    {
        try {
            $profileId = (int)$this->getRequest()->getParam(ProfileInterface::PROFILE_ID);
            $hash = $this->getRequest()->getParam(ProfileInterface::HASH);
            if ($hash) {
                $requestEntity = $this->profileRepository->getByHash($hash);
                if ($requestEntity->getProfileId() !== $profileId) {
                    throw new NotFoundException(__('Page not found.'));
                }
            } else {
                $requestEntity = $this->profileRepository->get($profileId);
            }
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $requestEntity;
    }

    /**
     * Check if action with profile is allowed
     *
     * @return bool
     */
    abstract protected function isActionAllowed();

    /**
     * Check if profile belongs to current customer
     *
     * @return bool
     * @throws NotFoundException
     */
    private function isProfileBelongsToCustomer()
    {
        $profile = $this->getProfile();
        if ($profile->getProfileId()
            && $profile->getCustomerId() == $this->customerSession->getCustomerId()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Redirect to back page
     *
     * @return Redirect
     */
    protected function redirectToBack()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Get params
     *
     * @param int $profileId
     * @param int|null $itemId
     * @return array
     */
    protected function getParams($profileId, $itemId = null)
    {
        $params = [ProfileInterface::PROFILE_ID => $profileId];

        if ($itemId) {
            $params = array_merge($params, [ProfileItemInterface::ITEM_ID => $itemId]);
        }

        $hash = $this->getRequest()->getParam(ProfileInterface::HASH);
        if ($hash) {
            $params = array_merge($params, [ProfileInterface::HASH => $hash]);
        }

        return $params;
    }
}
