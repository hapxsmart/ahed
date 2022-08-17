<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Addresses as AddressesBlock;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Orders as OrdersBlock;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Plan as PlanBlock;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\NextOrderAndPlan as NextOrderAndPlanBlock;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Products as ProductsBlock;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Info
 * @package Aheadworks\Sarp2\Block\Adminhtml\Subscription
 */
class Info extends Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'aw_sarp2_subscription_info';

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param StatusSource $statusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        StatusSource $statusSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
        $this->statusSource = $statusSource;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        return $this->profileRepository->get($profileId);
    }

    /**
     * Get profile status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $statusOptions = $this->statusSource->getOptions();
        return $statusOptions[$this->getProfile()->getStatus()];
    }

    /**
     * Get orders block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getOrdersHtml()
    {
        $ordersBlock = $this->getLayout()
            ->createBlock(OrdersBlock::class, 'aw_sarp2.subscription.orders');
        return $ordersBlock->toHtml();
    }

    /**
     * Get next order and subscription plan block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getNextOrderAndPlanHtml()
    {
        /** @var NextOrderAndPlanBlock $block */
        $block = $this->getLayout()
            ->createBlock(NextOrderAndPlanBlock::class, 'aw_sarp2.subscription.next_order_and_plan');
        return $block
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * Get products block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getProductsHtml()
    {
        /** @var ProductsBlock $productsBlock */
        $productsBlock = $this->getLayout()
            ->createBlock(ProductsBlock::class, 'aw_sarp2.subscription.products');
        return $productsBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * Get addresses, shipping and payment block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAddressesHtml()
    {
        /** @var AddressesBlock $addressesBlock */
        $addressesBlock = $this->getLayout()
            ->createBlock(AddressesBlock::class, 'aw_sarp2.subscription.addresses');
        return $addressesBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        /** @var \Magento\Theme\Block\Html\Title $pageTitle */
        $pageTitle = $this->getLayout()->getBlock('page.title');
        if ($pageTitle) {
            $pageTitle->setPageTitle(' ');
        }
        return parent::_beforeToHtml();
    }
}
