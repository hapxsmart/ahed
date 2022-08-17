<?php
namespace Aheadworks\Sarp2\Engine\Profile\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Checker\OfflinePayment;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class PaymentToken
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Checker
 */
class PaymentToken
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var OfflinePayment
     */
    private $offlinePaymentChecker;

    /**
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param OfflinePayment $offlinePaymentChecker
     */
    public function __construct(
        PaymentTokenRepositoryInterface $tokenRepository,
        ProfileRepositoryInterface $profileRepository,
        OfflinePayment $offlinePaymentChecker
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->profileRepository = $profileRepository;
        $this->offlinePaymentChecker = $offlinePaymentChecker;
    }

    /**
     * Validate profile payment token available
     *
     * @param int|ProfileInterface $profile
     * @return bool
     * @throws LocalizedException
     */
    public function check($profile)
    {
        $profile = $this->getProfile($profile);
        if (null == $profile->getPaymentTokenId()) {
            return $this->offlinePaymentChecker->check($profile->getPaymentMethod());
        }
        try {
            $token = $this->tokenRepository->get($profile->getPaymentTokenId());
            return $token->getIsActive();
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Retrieve profile
     *
     * @param int|ProfileInterface $profile
     * @return ProfileInterface
     * @throws LocalizedException
     */
    private function getProfile($profile) {
        return $profile instanceof ProfileInterface
            ? $profile
            : $this->profileRepository->get($profile);
    }
}
