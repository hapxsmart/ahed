<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface PrePaymentInfoInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface PrePaymentInfoInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const IS_INITIAL_FEE_PAID = 'is_initial_fee_paid';
    const IS_TRIAL_PAID = 'is_trial_paid';
    const IS_REGULAR_PAID = 'is_regular_paid';

    /**#@-*/

    /**
     * Get initial fee paid flag
     *
     * @return bool
     */
    public function getIsInitialFeePaid();

    /**
     * Set initial fee paid flag
     *
     * @param bool $isInitialFeePaid
     * @return $this
     */
    public function setIsInitialFeePaid($isInitialFeePaid);

    /**
     * Get trial payment paid flag
     *
     * @return bool
     */
    public function getIsTrialPaid();

    /**
     * Set trial payment paid flag
     *
     * @param bool $isTrialPaid
     * @return $this
     */
    public function setIsTrialPaid($isTrialPaid);

    /**
     * Get regular payment paid flag
     *
     * @return bool
     */
    public function getIsRegularPaid();

    /**
     * Set regular payment paid flag
     *
     * @param bool $isRegularPaid
     * @return $this
     */
    public function setIsRegularPaid($isRegularPaid);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\PrePaymentInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\PrePaymentInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\PrePaymentInfoExtensionInterface $extensionAttributes
    );
}
