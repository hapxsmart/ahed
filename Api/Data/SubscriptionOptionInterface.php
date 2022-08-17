<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SubscriptionOptionInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface SubscriptionOptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const OPTION_ID = 'option_id';
    const PRODUCT_ID = 'product_id';
    const PRODUCT = 'product';
    const PLAN_ID = 'plan_id';
    const PLAN = 'plan';
    const WEBSITE_ID = 'website_id';
    const INITIAL_FEE = 'initial_fee';
    const TRIAL_PRICE = 'trial_price';
    const REGULAR_PRICE = 'regular_price';
    const IS_AUTO_TRIAL_PRICE = 'is_auto_trial_price';
    const IS_AUTO_REGULAR_PRICE = 'is_auto_regular_price';
    const IS_INSTALLMENTS_MODE = 'is_installments_mode';
    const FRONTEND_TITLE = 'frontend_title';
    const BACKEND_TITLE = 'backend_title';
    /**#@-*/

    /**
     * Get option Id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option Id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get product Id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set product Id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct();

    /**
     * Set product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     */
    public function setProduct($product);

    /**
     * Get plan Id
     *
     * @return int
     */
    public function getPlanId();

    /**
     * Set plan Id
     *
     * @param int $planId
     * @return $this
     */
    public function setPlanId($planId);

    /**
     * Get plan
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanInterface
     */
    public function getPlan();

    /**
     * Set plan
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanInterface $plan
     * @return $this
     */
    public function setPlan($plan);

    /**
     * Get website Id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website Id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get initial fee
     *
     * @return float
     */
    public function getInitialFee();

    /**
     * Set initial fee
     *
     * @param float $initialFee
     * @return $this
     */
    public function setInitialFee($initialFee);

    /**
     * Get trial price
     *
     * @return float
     */
    public function getTrialPrice();

    /**
     * Set trial price
     *
     * @param float $trialPrice
     * @return $this
     */
    public function setTrialPrice($trialPrice);

    /**
     * Get regular price
     *
     * @return float
     */
    public function getRegularPrice();

    /**
     * Set regular price
     *
     * @param float $regularPrice
     * @return $this
     */
    public function setRegularPrice($regularPrice);

    /**
     * Get trial price auto calculation flag
     *
     * @return bool
     */
    public function getIsAutoTrialPrice();

    /**
     * Set trial price auto calculation flag
     *
     * @param bool $isAutoTrialPrice
     * @return $this
     */
    public function setIsAutoTrialPrice($isAutoTrialPrice);

    /**
     * Get regular price auto calculation flag
     *
     * @return bool
     */
    public function getIsAutoRegularPrice();

    /**
     * Set regular price auto calculation flag
     *
     * @param bool $isAutoRegularPrice
     * @return $this
     */
    public function setIsAutoRegularPrice($isAutoRegularPrice);

    /**
     * Get installments mode flag
     *
     * @return bool
     */
    public function getIsInstallmentsMode();

    /**
     * Set installments mode flag
     *
     * @param bool $isInstallmentsMode
     * @return $this
     */
    public function setIsInstallmentsMode($isInstallmentsMode);

    /**
     * Get frontend title
     *
     * @return string|null
     */
    public function getFrontendTitle();

    /**
     * Set frontend title
     *
     * @param string $frontendTitle
     * @return $this
     */
    public function setFrontendTitle($frontendTitle);

    /**
     * Get backend title
     *
     * @return string
     */
    public function getBackendTitle();

    /**
     * Set backend title
     *
     * @param string $backendTitle
     * @return $this
     */
    public function setBackendTitle($backendTitle);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\SubscriptionOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\SubscriptionOptionExtensionInterface $extensionAttributes
    );
}
