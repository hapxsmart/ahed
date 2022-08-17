<?php
namespace Aheadworks\Sarp2\Model\Access;

use Aheadworks\Sarp2\Api\Data\AccessTokenInterface;
use Aheadworks\Sarp2\Api\Data\AccessTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\Encryption\Encryptor;

/**
 * Class TokenFactory
 *
 * @package Aheadworks\Sarp2\Model\Access
 */
class TokenFactory
{
    /**
     * @var AccessTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param AccessTokenInterfaceFactory $tokenFactory
     * @param Encryptor $encryptor
     */
    public function __construct(
        AccessTokenInterfaceFactory $tokenFactory,
        Encryptor $encryptor
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * Create new access token for edit profile
     *
     * @param ProfileInterface $profile
     * @return AccessTokenInterface
     * @throws \Exception
     */
    public function create(ProfileInterface $profile)
    {
        /** @var AccessTokenInterface $token */
        $token = $this->tokenFactory->create();

        $token
            ->setProfileId($profile->getProfileId())
            ->setTokenValue($this->generateTokenValue($profile));

        return $token;
    }

    /**
     * Create token value hash
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Exception
     */
    protected function generateTokenValue(ProfileInterface $profile)
    {
        $now = (new \DateTime())->getTimestamp();
        $data = $profile->getProfileId() . uniqid($now);

        return $this->encryptor->hash($data, Encryptor::HASH_VERSION_MD5);
    }
}
