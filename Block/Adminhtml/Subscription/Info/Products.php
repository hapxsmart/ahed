<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Item\Checker\IsRemoveActionAvailable;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

class Products extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var IsRemoveActionAvailable
     */
    private $isRemoveActionAvailable;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info/products.phtml';

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrency $priceCurrency
     * @param IsRemoveActionAvailable $isRemoveActionAvailable
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        PriceCurrency $priceCurrency,
        IsRemoveActionAvailable $isRemoveActionAvailable,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->isRemoveActionAvailable = $isRemoveActionAvailable;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Check if profile item should be displayed
     *
     * @param ProfileItemInterface $item
     * @return bool
     */
    public function isItemShouldBeDisplayed($item)
    {
        if ($item->getParentItem()) {
            return false;
        }
        return true;
    }

    /**
     * Check if product exists
     *
     * @param int $productId
     * @return bool
     */
    public function isProductExists($productId)
    {
        try {
            $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * Get product edit url
     *
     * @param int $productId
     * @return string
     */
    public function getProductEditUrl($productId)
    {
        return $this->_urlBuilder->getUrl('catalog/product/edit', ['id' => $productId]);
    }

    /**
     * Format profile item  amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatProfileItemAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format(
            $amount,
            true,
            2,
            null,
            $currencyCode
        );
    }

    /**
     * Get edit item url
     *
     * @param int $profileId
     * @param int $itemId
     * @return string
     */
    public function getEditItemUrl($profileId, $itemId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/subscription_edit/item',
            [ProfileInterface::PROFILE_ID => $profileId, ProfileItemInterface::ITEM_ID => $itemId]);
    }

    /**
     * Check if remove action available
     *
     * @param ProfileInterface $profile
     * @param ProfileItemInterface $profileItem
     * @return bool
     * @throws LocalizedException
     */
    public function isRemoveActionAvailable(ProfileInterface $profile, ProfileItemInterface $profileItem): bool
    {
        return $this->isRemoveActionAvailable->check($profile, $profileItem);
    }

    /**
     * Get edit item url
     *
     * @param int $profileId
     * @param int $itemId
     * @return string
     */
    public function getRemoveItemUrl($profileId, $itemId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/subscription_save/removeItem',
            [ProfileInterface::PROFILE_ID => $profileId, ProfileItemInterface::ITEM_ID => $itemId]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
