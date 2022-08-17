<?php
namespace Aheadworks\Sarp2\Model\Vault\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Class BackupManagement
 *
 * @package Aheadworks\Sarp2\Model\Vault\Data
 */
class BackupManagement
{
    const REMOVED_VAULTS_BACKUP = 'removed_vaults_backup';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * PaymentTokenManagementPlugin constructor.
     *
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Backup gateway token from removable vault to new vault token details field
     *
     * @param PaymentTokenInterface $newVault
     * @param PaymentTokenInterface $removableVault
     */
    public function backupRemovableVaultGatewayToken($newVault, $removableVault)
    {
        $newVaultDetails = $this->serializer->unserialize($newVault->getTokenDetails() ?: '{}');

        $backup = $newVaultDetails[self::REMOVED_VAULTS_BACKUP] ?? [];
        $backup[] = $removableVault->getGatewayToken();
        foreach ($this->getBackupedGatewayTokens($removableVault) as $backupedGatewayToken) {
            $backup[] = $backupedGatewayToken;
        }
        $backup = array_unique($backup);

        $newVaultDetails[self::REMOVED_VAULTS_BACKUP] = $backup;

        $newVault->setTokenDetails($this->serializer->serialize($newVaultDetails));
    }

    /**
     * Get stored in additional field gateway tokens
     *
     * @param PaymentTokenInterface $vault
     * @return string[]
     */
    public function getBackupedGatewayTokens($vault)
    {
        $vaultDetails = $this->serializer->unserialize($vault->getTokenDetails() ?: '{}');

        return $vaultDetails[self::REMOVED_VAULTS_BACKUP] ?? [];
    }
}
