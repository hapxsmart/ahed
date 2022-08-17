<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Plan\Source\BillingFrequency as BillingFrequencySource;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

class NextOrderAndPlan extends Template
{
    /**
     * @var RepeatPaymentsConverter
     */
    private $repeatPaymentsConverter;

    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var BillingFrequencySource
     */
    private $billingFrequencySource;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info/next_order_and_plan.phtml';

    /**
     * @param Context $context
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param BillingPeriodSource $billingPeriodSource
     * @param BillingFrequencySource $billingFrequencySource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param PriceCurrency $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        BillingPeriodSource $billingPeriodSource,
        BillingFrequencySource $billingFrequencySource,
        RepeatPaymentsSource $repeatPaymentsSource,
        PriceCurrency $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
        $this->billingPeriodSource = $billingPeriodSource;
        $this->billingFrequencySource = $billingFrequencySource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->priceCurrency = $priceCurrency;
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
     * Get next order info block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getNextOrderInfoHtml()
    {
        /** @var NextOrderInfo $nextOrderInfoBlock */
        $nextOrderInfoBlock = $this->getLayout()
            ->createBlock(NextOrderInfo::class, 'aw_sarp2.subscription.next_order_and_plan.next_order_info');
        return $nextOrderInfoBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * Format repeat payments value
     *
     * @param string $period
     * @param int $frequency
     * @return \Magento\Framework\Phrase
     */
    public function formatRepeatValue($period, $frequency)
    {
        $repeatPaymentsOptions = $this->repeatPaymentsSource->getOptions();
        $billingFrequencyOptions = $this->billingFrequencySource->getOptions();
        $billingPeriodOptions = $this->billingPeriodSource->getOptions();

        $repeatPayments = $this->repeatPaymentsConverter->toRepeatPayments($period, $frequency);
        if ($repeatPayments) {
            return $repeatPaymentsOptions[$repeatPayments];
        }

        return __(
            'Every %1 %2',
            $billingFrequencyOptions[$frequency],
            $billingPeriodOptions[$period]
        );
    }

    /**
     * Format regular repeat value
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return \Magento\Framework\Phrase
     */
    public function formatRegularRepeatValue($planDefinition)
    {
        return $this->formatRepeatValue(
            $planDefinition->getBillingPeriod(),
            $planDefinition->getBillingFrequency()
        );
    }

    /**
     * Format trial repeat value
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return \Magento\Framework\Phrase
     */
    public function formatTrialRepeatValue($planDefinition)
    {
        return $this->formatRepeatValue(
            $planDefinition->getTrialBillingPeriod(),
            $planDefinition->getTrialBillingFrequency()
        );
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $planId
     * @return string
     */
    public function getPlanEditUrl($planId)
    {
        return $this->_urlBuilder->getUrl('aw_sarp2/plan/edit', ['plan_id' => $planId]);
    }

    /**
     * Get profile plan edit url
     *
     * @param int $profileId
     * @return string
     */
    public function getProfilePlanEditUrl(int $profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/subscription_edit/plan',
            [ProfileInterface::PROFILE_ID => $profileId]
        );
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
     * Format profile amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatProfileAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format($amount, true, 2, null, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        return $this->getProfile()
            ? parent::_toHtml()
            : '';
    }
}
