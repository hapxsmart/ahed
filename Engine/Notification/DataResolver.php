<?php
namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\LinkBuilder as ExtendLinkBuilder;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\ValidatorWrapper;
use Aheadworks\Sarp2\Model\Email\Template\PriceFormatter;
use Aheadworks\Sarp2\Model\Plan\Resolver\TitleResolver;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Aheadworks\Sarp2\Engine\Notification\Offer\Secure\LinkBuilder as SecureLinkBuilder;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class DataResolver
{
    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @var TitleResolver
     */
    private $titleResolver;

    /**
     * @var ValidatorWrapper
     */
    private $extendActionValidator;

    /**
     * @var ExtendLinkBuilder
     */
    private $extendLinkBuilder;

    /**
     * @var SecureLinkBuilder
     */
    private $secureLinkBuilder;

    /**
     * @param CoreDate $coreDate
     * @param TimezoneInterface $timezone
     * @param PriceFormatter $priceFormatter
     * @param TitleResolver $titleResolver
     * @param ValidatorWrapper $extendActionValidator
     * @param ExtendLinkBuilder $extendLinkBuilder
     * @param SecureLinkBuilder $secureLinkBuilder
     */
    public function __construct(
        CoreDate $coreDate,
        TimezoneInterface $timezone,
        PriceFormatter $priceFormatter,
        TitleResolver $titleResolver,
        ValidatorWrapper $extendActionValidator,
        ExtendLinkBuilder $extendLinkBuilder,
        SecureLinkBuilder $secureLinkBuilder
    ) {
        $this->coreDate = $coreDate;
        $this->timezone = $timezone;
        $this->priceFormatter = $priceFormatter;
        $this->titleResolver = $titleResolver;
        $this->extendActionValidator = $extendActionValidator;
        $this->extendLinkBuilder = $extendLinkBuilder;
        $this->secureLinkBuilder = $secureLinkBuilder;
    }

    /**
     * Resolve notification data
     *
     * @param ResolveSubject $subject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resolve(ResolveSubject $subject)
    {
        $sourcePayment = $subject->getSourcePayment();

        $profile = $sourcePayment->getProfile();
        $planDefinition = $profile->getPlanDefinition();
        $currencyCode = $profile->getProfileCurrencyCode();
        $isProfileLinkAvailable = $profile->getCustomerGroupId() !== CustomerGroup::NOT_LOGGED_IN_ID
            || $this->secureLinkBuilder->isSecureLinkAvailable($profile);
        $data = [
            'customerName' => $profile->getCustomerFullname(),
            'customerEmail' => $profile->getCustomerEmail(),
            'totalPaid' => $this->priceFormatter->format($sourcePayment->getTotalPaid(), $currencyCode),
            'totalScheduled' => $this->priceFormatter->format(
                $sourcePayment->getTotalScheduled(),
                $currencyCode
            ),
            'profileId' => $profile->getProfileId(),
            'incrementProfileId' => $profile->getIncrementId(),
            'orderId' => $sourcePayment->getOrderId(),
            'planName' => ($profile->getPlanId())?$this->titleResolver->getTitle($profile->getPlanId(), $profile->getStoreId()):$profile->getPlanName(),
            'displayAsInstallment' => $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT,
            'isFreeTrial' => $profile->getPaymentMethod() == PaymentInterface::FREE_PAYMENT_METHOD,
            'secureLink' => $this->secureLinkBuilder->build($profile),
            'isProfileLinkAvailable' => $isProfileLinkAvailable,
            'isProfileLinkUnavailable' => !$isProfileLinkAvailable
        ];

        $nextPayments = $subject->getNextPayments();
        if (count($nextPayments)) {
            $nearestPayment = $this->getNearestPayment($nextPayments);
            $timezone = $this->timezone->getConfigTimezone(
                ScopeInterface::SCOPE_STORE,
                $profile->getStoreId()
            );

            $data = array_merge(
                $data,
                [
                    'nextPaymentDate' => $this->timezone->formatDateTime(
                        new \DateTime($nearestPayment->getScheduledAt()),
                        \IntlDateFormatter::SHORT,
                        \IntlDateFormatter::NONE,
                        null,
                        $timezone
                    ),
                    'nextPaymentTotalAmount' => $this->priceFormatter->format(
                        $nearestPayment->getTotalScheduled(),
                        $currencyCode
                    )
                ]
            );
        }

        $extendLink = $this->getExtendLink($profile);
        if ($extendLink) {
            $data['extendLink'] = $extendLink;
        }

        return $data;
    }

    /**
     * Retrieve extend subscription link if it possible
     *
     * @param ProfileInterface $profile
     * @return string|null
     */
    private function getExtendLink(ProfileInterface $profile)
    {
        if ($this->extendActionValidator->isValid($profile)) {
            return $this->extendLinkBuilder->build($profile);
        }

        return null;
    }

    /**
     * Get nearest payment
     *
     * @param PaymentInterface[] $nextPayments
     * @return PaymentInterface
     */
    private function getNearestPayment($nextPayments)
    {
        reset($nextPayments);
        /** @var PaymentInterface $nearestPayment */
        $nearestPayment = current($nextPayments);
        if (count($nextPayments) > 1) {

            /**
             * @param PaymentInterface $payment
             * @return void
             */
            $callback = function ($payment) use (&$nearestPayment) {
                if ($payment != $nearestPayment) {
                    $baseTm = $this->coreDate->gmtTimestamp($nearestPayment->getScheduledAt());
                    $currentTm = $this->coreDate->gmtTimestamp($payment->getScheduledAt());
                    if ($currentTm < $baseTm) {
                        $nearestPayment = $payment;
                    }
                }
            };
            array_walk($nextPayments, $callback);
        }
        return $nearestPayment;
    }
}
