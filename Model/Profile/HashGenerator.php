<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Magento\Framework\Encryption\Encryptor;

/**
 * Class HashGenerator
 */
class HashGenerator
{
    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param Encryptor $encryptor
     */
    public function __construct(
        Encryptor $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Generate hash
     *
     * @param int $profileId
     * @return string
     */
    public function generate($profileId)
    {
        $now = (new \DateTime())->getTimestamp();
        $data = $profileId . uniqid($now);

        return $this->encryptor->hash($data, Encryptor::HASH_VERSION_SHA256);
    }
}
