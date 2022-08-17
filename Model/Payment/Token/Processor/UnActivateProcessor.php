<?php
namespace Aheadworks\Sarp2\Model\Payment\Token\Processor;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder as TokenFinder;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Exception\LocalizedException;

class UnActivateProcessor
{
    /**
     * @var TokenFinder
     */
    private $tokenFinder;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var ProfileFinder
     */
    private $profileFinder;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var string[]
     */
    private $excludePaymentMethod = [
        'anet_creditcard'
    ];

    /**
     * Processor constructor.
     *
     * @param TokenFinder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param ProfileFinder $profileFinder
     * @param ProfileManagementInterface $profileManagement
     * @param array $excludePaymentMethod
     */
    public function __construct(
        TokenFinder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository,
        ProfileFinder $profileFinder,
        ProfileManagementInterface $profileManagement,
        array $excludePaymentMethod = []
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
        $this->profileFinder = $profileFinder;
        $this->profileManagement = $profileManagement;
        $this->excludePaymentMethod = array_merge($this->excludePaymentMethod, $excludePaymentMethod);
    }

    /**
     * @param $tokenValue
     * @return ProfileInterface[]
     */
    public function unActivateTokenAndSuspendRelatedProfiles($tokenValue)
    {
        $suspendedProfiles = [];

        $token = $this->findToken($tokenValue);
        if (
            $token
            && !$this->isExcludedMethod($token->getPaymentMethod())
        ) {
            try {
                $token->setIsActive(false);
                $this->tokenRepository->save($token);

                foreach ($this->findRelatedProfiles($token) as $profile) {
                    try {
                        $allowedStatuses = $this->profileManagement->getAllowedStatuses($profile->getProfileId());
                        $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());

                        if (in_array(Status::SUSPENDED, $allowedStatuses)
                            && $nextPaymentInfo->getPaymentStatus() !=
                                ScheduledPaymentInfoInterface::PAYMENT_STATUS_LAST_PERIOD_HOLDER) {
                            $this->profileManagement->changeStatusAction(
                                $profile->getProfileId(),
                                Status::SUSPENDED
                            );
                            $suspendedProfiles[] = $profile;
                        }
                    } catch (LocalizedException $exception) {
                        continue;
                    }
                }
            } catch (LocalizedException $exception) {
                return $suspendedProfiles;
            }
        }

        return $suspendedProfiles;
    }

    /**
     * Find token by token value
     *
     * @param $tokenValue
     * @return PaymentTokenInterface|null
     */
    private function findToken($tokenValue)
    {
        try {
            return $this->tokenFinder->findExistingByValue($tokenValue);
        } catch (LocalizedException $exception) {
            return null;
        }
    }

    /**
     * Find profiles by payment token
     *
     * @param PaymentTokenInterface $token
     * @return ProfileInterface[]
     */
    private function findRelatedProfiles($token)
    {
        try {
            $profiles = $this->profileFinder->getByTokenId($token->getTokenId());
        } catch (LocalizedException $exception) {
            $profiles = [];
        }

        return $profiles;
    }

    /**
     * Check if method is excluded for processing
     *
     * @param string $methodCode
     * @return bool
     */
    private function isExcludedMethod($methodCode)
    {
        return in_array($methodCode, $this->excludePaymentMethod);
    }
}
