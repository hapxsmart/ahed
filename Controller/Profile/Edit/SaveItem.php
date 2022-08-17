<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\CouldNotUpdateProduct;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class SaveItem
 */
class SaveItem extends AbstractProfile
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param ProfileManagementInterface $profileManagement
     * @param RequestQuantityProcessor $quantityProcessor
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        ProfileManagementInterface $profileManagement,
        RequestQuantityProcessor $quantityProcessor
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->profileManagement = $profileManagement;
        $this->quantityProcessor = $quantityProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $profile = $item = null;
        try {
            $profile = $this->getProfile();
            $item = $this->getItem($profile);
            $isOneTimeOnly = $this->getRequest()->getParam('is_one_time_only', false);
            $buyRequest = $this->getRequest()->getParams();

            $buyRequest = $this->quantityProcessor->process($buyRequest);

            $this->profileManagement->changeProductItem(
                $profile->getProfileId(),
                $item->getItemId(),
                $buyRequest,
                $isOneTimeOnly
            );

            $this->messageManager->addSuccessMessage(__('The subscription has been updated successfully.'));
            return $this->resultRedirectFactory->create()
                ->setPath('*/*/index', $this->getParams($profile->getProfileId()));
        } catch (CouldNotUpdateProduct $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Couldn’t update subscription, please try again later.')
            );
        }

        if ($profile && $item) {
            return $this->resultRedirectFactory->create()
                ->setPath('*/profile_edit/item', $this->getParams($profile->getProfileId(), $item->getItemId()));
        } else {
            return $this->resultRedirectFactory->create()
                ->setPath('*/*/index', $this->getParams($profile->getProfileId()));
        }
    }

    /**
     * Retrieve product item
     *
     * @param ProfileInterface $profile
     * @return ProfileItemInterface
     * @throws NoSuchEntityException
     */
    private function getItem($profile)
    {
        $itemId = (int)$this->getRequest()->getParam(ProfileItemInterface::ITEM_ID);
        foreach ($profile->getItems() as $item) {
            if ($item->getItemId() == $itemId) {
                return $item;
            }
        }

        throw new NoSuchEntityException(__('Product item not found.'));
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isEditProductItemActionAvailable($profileId);
    }
}
