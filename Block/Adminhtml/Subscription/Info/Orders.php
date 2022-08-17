<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Aheadworks\Sarp2\Model\Profile\View\Adminhtml\OrderInfo;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Orders\Pager;

class Orders extends Template
{
    /**
     * Profile orders list page size
     */
    const PAGE_SIZE = 10;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @var int
     */
    private $profileId;

    /**
     * @var int
     */
    private $page;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var OrderInfo
     */
    private $orderInfo;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info/orders.phtml';

    /**
     * @param Context $context
     * @param PriceCurrency $priceCurrency
     * @param OrderConfig $orderConfig
     * @param ProfileRepositoryInterface $profileRepository
     * @param OrderInfo $orderInfo
     * @param array $data
     */
    public function __construct(
        Context $context,
        PriceCurrency $priceCurrency,
        OrderConfig $orderConfig,
        ProfileRepositoryInterface $profileRepository,
        OrderInfo $orderInfo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->orderConfig = $orderConfig;
        $this->profileRepository = $profileRepository;
        $this->orderInfo = $orderInfo;
    }

    /**
     * Set profile ID
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * Set current page number
     *
     * @param int $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get profile ID
     *
     * @return int|null
     */
    private function getProfileId()
    {
        return $this->profileId ? : $this->getRequest()->getParam('profile_id');
    }

    /**
     * Get current page
     *
     * @return int
     * @throws LocalizedException
     */
    private function getPage()
    {
        if (!$this->page) {
            $page = $this->getRequest()->getParam('page', 1);
            $lastPageNum = $this->getLastPageNum();

            if (!is_numeric($page) || $page < 1) {
                $this->page = 1;
            } elseif ($page > $lastPageNum) {
                $this->page = $lastPageNum;
            } else {
                $this->page = (int)$page;
            }
        }
        return $this->page;
    }

    /**
     * Get last page num
     *
     * @return int
     * @throws LocalizedException
     */
    private function getLastPageNum()
    {
        return (int)ceil($this->getTotalProfileOrdersCount() / self::PAGE_SIZE);
    }

    /**
     * Get profile orders
     *
     * @return ProfileOrderInterface[]
     * @throws LocalizedException
     */
    public function getProfileOrders()
    {
        $profileId = $this->getProfileId();
        $page = $this->getPage();
        $searchCriteriaBuilder = $this->orderInfo->getSearchCriteriaBuilder();
        $searchCriteriaBuilder
            ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId)
            ->setPageSize(self::PAGE_SIZE)
            ->setCurrentPage($page);

        return $this->orderInfo->getProfileOrders($profileId);
    }

    /**
     * Get total profile orders count
     *
     * @return int
     * @throws LocalizedException
     */
    public function getTotalProfileOrdersCount()
    {
        return $this->orderInfo->getTotalProfileOrdersCount($this->getProfileId());
    }

    /**
     * Get left orders count
     *
     * @return bool|int
     */
    public function getOrdersLeftCount()
    {
        return $this->orderInfo->getOrdersLeftCount($this->getProfileId());
    }

    /**
     * Get displayed orders numbers
     *
     * @return string
     * @throws LocalizedException
     */
    public function getDisplayedOrdersNumbers()
    {
        $totalOrders = $this->getTotalProfileOrdersCount();
        if ($totalOrders > self::PAGE_SIZE) {
            $pageCount = ceil($totalOrders / self::PAGE_SIZE);
            if ($this->getPage() < $pageCount) {
                $frameStart = self::PAGE_SIZE * ($this->getPage() - 1) + 1;
                $frameEnd = self::PAGE_SIZE * ($this->getPage() - 1) + self::PAGE_SIZE;
                $displayedNumbers = $frameStart . '-' . $frameEnd;
            } else {
                $frameStart = self::PAGE_SIZE * ($this->getPage() - 1) + 1;
                $displayedNumbers = $frameStart . '-' . $totalOrders;
            }
        } else {
            $displayedNumbers = $totalOrders;
        }
        return $displayedNumbers;
    }

    /**
     * Get admin date
     *
     * @param string $date
     * @return \DateTime
     */
    public function getAdminDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }

    /**
     * Get order view url
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $this->_urlBuilder->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Format order amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatOrderAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format($amount, true, 2, null, $currencyCode);
    }

    /**
     * Get order status label
     *
     * @param string $status
     * @return string
     * @throws LocalizedException
     */
    public function getOrderStatusLabel($status)
    {
        return $this->orderConfig->getStatusLabel($status);
    }

    /**
     * Render pager
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getLayout()->createBlock(Pager::class, 'orders_pager');

        /* @var $pagerBlock Pager */
        $pagerBlock
            ->setTemplate('Aheadworks_Sarp2::subscription/info/orders/pager.phtml')
            ->setCurrentPage($this->getPage())
            ->setItemsCount($this->getTotalProfileOrdersCount())
            ->setPageSize(self::PAGE_SIZE);

        return $pagerBlock->toHtml();
    }

    /**
     * Get next order info html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getNextOrderInfoHtml()
    {
        /** @var NextOrderInfo $nextOrderInfoBlock */
        $nextOrderInfoBlock = $this->getLayout()
            ->createBlock(NextOrderInfo::class, 'aw_sarp2.subscription.orders.next_order_info');
        return $nextOrderInfoBlock
            ->setTemplate('Aheadworks_Sarp2::subscription/info/orders/next_order_info.phtml')
            ->setProfile($this->profileRepository->get($this->getProfileId()))
            ->toHtml();
    }
}
