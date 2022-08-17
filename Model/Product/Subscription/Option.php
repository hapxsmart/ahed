<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionExtensionInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Validator;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Option
 * @package Aheadworks\Sarp2\Model\Product\Subscription
 */
class Option extends AbstractModel implements SubscriptionOptionInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Validator $validator
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Validator $validator,
        ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->validator = $validator;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(OptionResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        if ($this->getData(self::PRODUCT) === null) {
            $optionId = $this->getOptionId();
            if ($optionId) {
                $productId = $this->getResource()->getProductEntityId($optionId);
                $this->setData(
                    self::PRODUCT,
                    $this->productRepository->getById($productId)
                );
            }
        }
        return $this->getData(self::PRODUCT);
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct($product)
    {
        return $this->setData(self::PRODUCT, $product);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlanId()
    {
        return $this->getData(self::PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanId($planId)
    {
        return $this->setData(self::PLAN_ID, $planId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlan()
    {
        return $this->getData(self::PLAN);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlan($plan)
    {
        return $this->setData(self::PLAN, $plan);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialFee()
    {
        return $this->getData(self::INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialFee($initialFee)
    {
        return $this->setData(self::INITIAL_FEE, $initialFee);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPrice()
    {
        return $this->getData(self::TRIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPrice($trialPrice)
    {
        return $this->setData(self::TRIAL_PRICE, $trialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPrice()
    {
        return $this->getData(self::REGULAR_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularPrice($regularPrice)
    {
        return $this->setData(self::REGULAR_PRICE, $regularPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAutoTrialPrice()
    {
        return $this->getData(self::IS_AUTO_TRIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAutoTrialPrice($isAutoTrialPrice)
    {
        return $this->setData(self::IS_AUTO_TRIAL_PRICE, $isAutoTrialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAutoRegularPrice()
    {
        return $this->getData(self::IS_AUTO_REGULAR_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAutoRegularPrice($isAutoRegularPrice)
    {
        return $this->setData(self::IS_AUTO_REGULAR_PRICE, $isAutoRegularPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsInstallmentsMode()
    {
        return (bool)$this->getData(self::IS_INSTALLMENTS_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInstallmentsMode($isInstallmentsMode)
    {
        return $this->setData(self::IS_INSTALLMENTS_MODE, $isInstallmentsMode);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendTitle()
    {
        return $this->getData(self::FRONTEND_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFrontendTitle($frontendTitle)
    {
        return $this->setData(self::FRONTEND_TITLE, $frontendTitle);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendTitle()
    {
        return $this->getData(self::BACKEND_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackendTitle($backendTitle)
    {
        return $this->setData(self::BACKEND_TITLE, $backendTitle);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(SubscriptionOptionExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
