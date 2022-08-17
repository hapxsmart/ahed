<?php
namespace Aheadworks\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Model\Profile\Registry as RegistryProvider;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Helper\Product\View as ProductView;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Item
 */
class Item extends AbstractProfile implements ViewInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProductView
     */
    private $productView;

    /**
     * @var RegistryProvider
     */
    private $profileRegistry;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param PageFactory $resultPageFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param ProductView $productView
     * @param RegistryProvider $profileRegistry
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        PageFactory $resultPageFactory,
        DataObjectFactory $dataObjectFactory,
        ProductView $productView,
        RegistryProvider $profileRegistry
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->productView = $productView;
        $this->profileRegistry = $profileRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $profile = $this->getProfile();
            $item = $this->getItem($profile);
            $productId = $item->getProductId();

            $this->getRequest()->setParam('id', $productId);
            $this->getRequest()->setParam('product_id', $productId);
            $this->profileRegistry
                ->setProfile($profile)
                ->setProfileItem($item);

            $resultPage = $this->resultPageFactory->create();
            $this->productView->prepareAndRender(
                $resultPage,
                $productId,
                $this,
                $this->createParams($item)
            );
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while edited the Product.'));
        }

        return $this->redirectToBack();
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
        $itemId = (int)$this->getRequest()->getParam('item_id');
        foreach ($profile->getItems() as $item) {
            if ($item->getItemId() == $itemId) {
                return $item;
            }
        }

        throw new NoSuchEntityException(__('Product item not found.'));
    }

    /**
     * Create byRequest params object
     *
     * @param ProfileItemInterface $item
     * @return DataObject
     */
    private function createParams($item)
    {
        $params = $this->dataObjectFactory->create();
        $params->setCategoryId(false);
        $params->setConfigureMode(true);
        $byuRequest = $this->dataObjectFactory->create([
            'data' => $item->getProductOptions()['info_buyRequest'] ?? []
        ]);
        $byuRequest->setQty($item->getQty());
        $params->setBuyRequest($byuRequest);

        return $params;
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
