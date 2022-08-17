<?php
namespace Aheadworks\Sarp2\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Configuration path to max retries count
     */
    const XML_PATH_MAX_RETRIES_COUNT = 'aw_sarp2/engine/max_retries_count';

    /**
     * Configuration path to default shipping method
     */
    const XML_PATH_DEFAULT_SHIPPING_METHOD = 'aw_sarp2/general/default_shipping_method';

    /**
     * Configuration path to log enabled flag
     */
    const XML_PATH_LOG_ENABLED = 'aw_sarp2/general/log_enabled';

    /**
     * Configuration path to subscribe and save
     */
    const XML_PATH_SUBSCRIBE_AND_SAVE_TEXT = 'aw_sarp2/product_page/subscribe_and_save';

    /**
     * Configuration path to subscribe and save tooltip
     */
    const XML_PATH_SUBSCRIBE_AND_SAVE_TOOLTIP = 'aw_sarp2/product_page/subscribe_and_save_tooltip';

    /**
     * Configuration path to subscription options renderer
     */
    const XML_PATH_SUBSCRIPTION_OPTIONS_RENDERER = 'aw_sarp2/product_page/subscription_options_renderer';

    /**
     * Configuration path to email identity
     */
    const XML_PATH_EMAIL_IDENTITY = 'aw_sarp2/email_settings/email_identity';

    /**
     * Configuration path to upcoming billing email offset
     */
    const XML_PATH_UPCOMING_BILLING_EMAIL_OFFSET = 'aw_sarp2/email_settings/upcoming_billing_email_days_offset';

    /**
     * Configuration path subscription editing - can switch to another plan
     */
    const XML_PATH_CAN_SWITCH_TO_ANOTHER_PLAN = 'aw_sarp2/subscription_editing/can_switch_to_another_plan';

    /**
     * Configuration path subscription editing - can change subscription address
     */
    const XML_PATH_CAN_CHANGE_SUBSCRIPTION_ADDRESS = 'aw_sarp2/subscription_editing/can_change_shipping_address';

    /**
     * Configuration path subscription editing - can edit next payment date
     */
    const XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE = 'aw_sarp2/subscription_editing/can_edit_next_payment_date';

    /**
     * Configuration path subscription editing - earliest next payment date
     */
    const XML_PATH_EARLIEST_NEXT_PAYMENT_DATE = 'aw_sarp2/subscription_editing/earliest_next_payment_date';

    /**
     * Configuration path subscription editing - can edit next payment date for membership
     */
    const XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE_FOR_MEMBERSHIP
        = 'aw_sarp2/subscription_editing/can_edit_next_payment_date_for_membership';

    /**
     * Configuration path subscription editing - can edit product item
     */
    const XML_PATH_CAN_EDIT_PRODUCT_ITEM = 'aw_sarp2/subscription_editing/can_edit_product_item';

    /**
     * Configuration path subscription editing - can edit product item one-time
     */
    const XML_PATH_CAN_ONE_TIME_EDIT_PRODUCT_ITEM = 'aw_sarp2/subscription_editing/can_one_time_edit_product_item';

    /**
     * Configuration path general - extend subscription period details
     */
    const XML_PATH_ALTRENATIVE_SUBSCRIPTION_PERIOD_DETAILS_VIEW =
        'aw_sarp2/product_page/alternative_subscription_period_details_view';

    /**
     * Configuration path general - Use Product Advanced Pricing
     */
    const XML_PATH_IS_USED_ADVANCED_PRICING = 'aw_sarp2/general/is_used_advanced_pricing';

    /**
     * Configuration path general - Recalculation of Totals
     */
    const XML_PATH_RECALCULATION_OF_TOTALS = 'aw_sarp2/general/recalculation_of_totals';

    /**
     * Configuration path product_page - Use Subscription price in "As low as"
     */
    const XML_PATH_IS_USED_SUBSCRIPTION_PRICE_IN_AS_LOW_AS =
        'aw_sarp2/product_page/is_used_subscription_price_in_as_low_as';

    /**
     * Configuration path subscription editing - can cancel subscription
     */
    const XML_PATH_CAN_CANCEL_SUBSCRIPTION = 'aw_sarp2/subscription_editing/can_cancel_subscription';

    /**
     * Configuration path email settings - failed billing bcc
     */
    const XML_PATH_EMAIL_FAILED_BILLING_BCC = 'aw_sarp2/email_settings/failed_billing_email_bcc';

    /**
     * Configuration path email settings - failed billing bcc
     */
    const XML_PATH_EMAIL_FAILED_BILLING_ADMIN = 'aw_sarp2/email_settings/failed_billing_admin_email';

    /**
     * Configuration path email settings - send secure link to
     */
    const XML_PATH_EMAIL_SEND_SECURE_LINK_TO = 'aw_sarp2/email_settings/send_secure_link_to';

    /**
     * Configuration path allow to add to nearest subscription
     */
    const XML_PATH_CAN_ADD_TO_NEAREST_SUBSCRIPTION = 'aw_sarp2/subscription_editing/can_add_to_nearest_subscription';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get max retries count for failed payment
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMaxRetriesCount($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MAX_RETRIES_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default shipping method
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDefaultShippingMethod($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_SHIPPING_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if logging enabled
     *
     * @return bool
     */
    public function isLogEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LOG_ENABLED);
    }

    /**
     * Get email identity
     *
     * @param int $storeId
     * @return string
     */
    public function getEmailIdentity($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sender data
     *
     * @param int $storeId
     * @return array
     */
    public function getSenderData($storeId)
    {
        $emailIdentity = $this->getEmailIdentity($storeId);
        return $this->scopeConfig->getValue(
            'trans_email/ident_' . $emailIdentity,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get upcoming billing email offset
     *
     * @param int $storeId
     * @return int
     */
    public function getUpcomingBillingEmailOffset($storeId)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_UPCOMING_BILLING_EMAIL_OFFSET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can switch to another plan
     *
     * @return int
     */
    public function canSwitchToAnotherPlan()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_SWITCH_TO_ANOTHER_PLAN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Can change subscription address
     *
     * @return int
     */
    public function canChangeSubscriptionAddress()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_CHANGE_SUBSCRIPTION_ADDRESS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Can edit next payment date
     *
     * @param int|null $websiteId
     * @return int
     */
    public function canEditNextPaymentDate($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Can edit next payment date
     *
     * @param int|null $storeId
     * @return int
     */
    public function getEarliestNextPaymentDate($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_EARLIEST_NEXT_PAYMENT_DATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can edit next payment date for membership
     *
     * @param int|null $websiteId
     * @return int
     */
    public function canEditNextPaymentDateForMembership($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_EDIT_NEXT_PAYMENT_DATE_FOR_MEMBERSHIP,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Can edit product item
     *
     * @param int|null $websiteId
     * @return int
     */
    public function canEditProductItem($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_EDIT_PRODUCT_ITEM,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Can one-time edit product item
     *
     * @param int|null $websiteId
     * @return int
     */
    public function canOneTimeEditProductItem($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CAN_ONE_TIME_EDIT_PRODUCT_ITEM,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve subscribe and save text config
     *
     * @param null $storeId
     * @return string
     */
    public function getSubscribeAndSaveText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBSCRIBE_AND_SAVE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve subscribe and save tooltip text config
     *
     * @param null $storeId
     * @return string
     */
    public function getSubscribeAndSaveTooltip($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBSCRIBE_AND_SAVE_TOOLTIP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve subscription options renderer config
     *
     * @param null $storeId
     * @return string
     */
    public function getSubscriptionOptionsRenderer($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SUBSCRIPTION_OPTIONS_RENDERER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can one-time edit product item
     *
     * @param int|null $storeId
     * @return int
     */
    public function isAlternativeSubscriptionDetailsView($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ALTRENATIVE_SUBSCRIPTION_PERIOD_DETAILS_VIEW,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can use advanced pricing
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isUsedAdvancedPricing($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_USED_ADVANCED_PRICING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is recalculation of totals enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isRecalculationOfTotalsEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_RECALCULATION_OF_TOTALS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if need use subscription price in as low as price
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isUsedSubscriptionPriceInAsLowAs($storeId = null)
    {
        // todo: M2SARP2-990 Hide "As Low As" price in release 2.12
        /*return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_USED_SUBSCRIPTION_PRICE_IN_AS_LOW_AS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );*/

        return false;
    }

    /**
     * Can cancel subscription
     *
     * @param int|null $websiteId
     * @return bool
     */
    public function canCancelSubscription($websiteId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CAN_CANCEL_SUBSCRIPTION,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve failed billing bcc
     *
     * @param null $storeId
     * @return string[]
     */
    public function getFailedBillingBCCEmail($storeId = null)
    {
        $emails = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_FAILED_BILLING_BCC,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!is_string($emails)) {
            return [];
        } else {
            return explode(',', $emails);
        }
    }

    /**
     * Retrieve failed billing admin email
     *
     * @param null $storeId
     * @return string
     */
    public function getFailedBillingAdminEmail($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_FAILED_BILLING_ADMIN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve who will get secure link
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSendSecureLinkTo($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SEND_SECURE_LINK_TO,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Can add to nearest subscription
     *
     * @param int|null $websiteId
     * @return bool
     */
    public function canAddToNearestSubscription($websiteId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_CAN_ADD_TO_NEAREST_SUBSCRIPTION,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
