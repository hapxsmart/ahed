<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProviderPool as ConfigProviderPool;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend as FrontendSubscriptionOptionSource;
use Aheadworks\Sarp2\Model\Profile\Details\Formatter as DetailsFormatter;
use Aheadworks\Sarp2\Model\UrlBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder as ProductImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Plan
 */
class Plan extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var ProductImageBuilder
     */
    private $productImageBuilder;

    /**
     * @var FrontendSubscriptionOptionSource
     */
    private $subscriptionOptionSource;

    /**
     * todo: consider move all 'configs' into \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface
     *       or another data interface. This will make available it on Web API layer
     * @var ConfigProviderPool
     */
    private $configProviderPool;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var DetailsFormatter
     */
    private $detailsFormatter;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param CurrencyFactory $currencyFactory
     * @param ProductImageBuilder $productImageBuilder
     * @param FrontendSubscriptionOptionSource $subscriptionOptionSource
     * @param ConfigProviderPool $configProviderPool
     * @param JsonSerializer $serializer
     * @param PlanRepositoryInterface $planRepository
     * @param DetailsFormatter $detailsFormatter
     * @param UrlBuilder $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        CurrencyFactory $currencyFactory,
        ProductImageBuilder $productImageBuilder,
        FrontendSubscriptionOptionSource $subscriptionOptionSource,
        ConfigProviderPool $configProviderPool,
        JsonSerializer $serializer,
        PlanRepositoryInterface $planRepository,
        DetailsFormatter $detailsFormatter,
        UrlBuilder $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->productUrl = $productUrl;
        $this->currencyFactory = $currencyFactory;
        $this->productImageBuilder = $productImageBuilder;
        $this->subscriptionOptionSource = $subscriptionOptionSource;
        $this->configProviderPool = $configProviderPool;
        $this->serializer = $serializer;
        $this->planRepository = $planRepository;
        $this->detailsFormatter = $detailsFormatter;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->registry->registry('profile');
    }

    /**
     * Retrieve product
     *
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Retrieve save url
     *
     * @param int $profileId
     * @return string
     */
    public function getSaveUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/savePlan',
            $this->urlBuilder->getParams($profileId, $this->getRequest())
        );
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
            $this->getProduct($productId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve product image html
     *
     * @param int $productId
     * @return string
     */
    public function getProductImageHtml($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        return $this->productImageBuilder->setProduct($product)
            ->setImageId('product_base_image')
            ->create()
            ->toHtml();
    }

    /**
     * Check if product has url
     *
     * @param int $productId
     * @return bool
     */
    public function hasProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get product url
     *
     * @param int $productId
     * @return string
     */
    public function getProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        return $this->productUrl->getUrl($product);
    }

    /**
     * Retrieve default option id
     *
     * @return int|null
     */
    public function getDefaultOptionId()
    {
        return $this->getProfile()->getPlanId();
    }

    /**
     * Get subscription option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $intersectOptionArray = [];
        foreach ($this->getProfile()->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $productOptions = $item->getProductOptions();
            if (!isset($productOptions['aw_sarp2_subscription_option'])) {
                continue;
            }
            $options = $this->subscriptionOptionSource->getPlanOptionArray($item->getProductId());
            $intersectOptionArray = $intersectOptionArray
                ? array_intersect_key($options, $intersectOptionArray)
                : $options;
        }

        return $intersectOptionArray;
    }

    /**
     * Get config data
     *
     * @return array
     */
    public function getConfigData()
    {
        $newDetails = [];
        foreach ($this->getProfile()->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $productId = $item->getProductId();
            $product = $this->getProduct($productId);
            $productTypeId = $product->getTypeId();
            $productSubscriptionDetails = $this->configProviderPool
                ->getConfigProvider($productTypeId)
                ->getSubscriptionDetailsConfig($productId, $item, $this->getProfile());
            $productSubscriptionDetails = $this->updateAmountAccordingItemQty(
                $productSubscriptionDetails,
                $item->getQty()
            );

            foreach ($productSubscriptionDetails as $planId => $details) {
                $trial = $this->getSubscriptionDetailsByType($details, Processor::TRIAL);
                if (!isset($newDetails[$planId])) {
                    if ($this->getDefaultOptionId() == $planId && $trial) {
                        $newDetails[$planId][Processor::TRIAL] = $trial;
                    }

                    $newDetails[$planId][Processor::REGULAR] = $this->getSubscriptionDetailsByType(
                        $details,
                        Processor::REGULAR
                    );
                } else {
                    foreach ($newDetails[$planId] as &$newDetail) {
                        $type = $newDetail['type'];
                        if ($type == Processor::REGULAR
                            || $type == Processor::TRIAL
                        ) {
                            $productDetail = $this->getSubscriptionDetailsByType($details, $type);
                            $newDetail['finalAmount'] += $productDetail['finalAmount'];
                        }
                    }
                }
            }
        }

        $configData = [
            'regularPrices' => ['options' => []],
            'subscriptionDetails' => $this->formatNewDetailsValues($newDetails, $this->getProfile()),
            'productType' => Product\Type::TYPE_SIMPLE,
            'productId' => 1
        ];

        return $this->serializer->serialize($configData);
    }

    /**
     * Retrieve subscription details by type
     *
     * @param array $details
     * @param string $type
     * @return null
     */
    private function getSubscriptionDetailsByType($details, $type)
    {
        foreach ($details as $detail) {
            if ($detail['type'] == $type) {
                return $detail;
            }
        }
        return null;
    }

    /**
     * Update details amount according item qty
     *
     * @param array[] $details
     * @param ProfileItemInterface $item
     * @return array
     */
    private function updateAmountAccordingItemQty($details, $qty)
    {
        return array_map(function ($detailsList) use ($qty) {
            return array_map(function ($details) use ($qty) {
                if (isset($details['finalAmount'])) {
                    $details['finalAmount'] *= $qty;
                }
                return $details;
            }, $detailsList);
        }, $details);
    }

    /**
     * Format new subscription details
     *
     * @param array[] $newDetails
     * @param ProfileInterface $profile
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function formatNewDetailsValues($newDetails, $profile)
    {
        $currency = $profile->getProfileCurrencyCode();
        array_walk($newDetails, function (&$detailsList, $planId) use ($currency) {
            $planDefinition = $this->getPlanDefinition($planId);
            $detailsList = array_map(function ($details) use ($planDefinition, $currency) {
                $detailsType = $details['type'];
                $billingCycles = $details['cycles'];

                if ($detailsType == Processor::REGULAR) {
                    $details['value'] = $this->detailsFormatter->getRegularPriceAndCycles(
                        $details['finalAmount'],
                        $planDefinition,
                        $billingCycles > 0,
                        false,
                        $currency
                    );
                }
                if ($detailsType == Processor::TRIAL) {
                    $details['value'] = $this->detailsFormatter->getTrialPriceAndCycles(
                        $details['finalAmount'],
                        $planDefinition,
                        $billingCycles > 0,
                        false,
                        $currency
                    );
                }
                return $details;
            }, $detailsList);
        });

        return $newDetails;
    }

    /**
     * Retrieve plan definition
     *
     * @param int $planId
     * @return \Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getPlanDefinition($planId)
    {
        $plan = $this->planRepository->get($planId);

        return $plan->getDefinition();
    }

    /**
     * Serialize data to json string
     *
     * @param mixed $data
     * @return bool|false|string
     */
    public function jsonEncode($data)
    {
        return $this->serializer->serialize($data);
    }
}
