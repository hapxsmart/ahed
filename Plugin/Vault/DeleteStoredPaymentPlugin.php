<?php
namespace Aheadworks\Sarp2\Plugin\Vault;

use Aheadworks\Sarp2\Model\Payment\Token\Processor\UnActivateProcessor as DeleteTokenProcessor;
use Aheadworks\Sarp2\Model\Vault\Data\BackupManagement as VaultBackupManagement;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\UrlInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/**
 * Class DeleteStoredPaymentPlugin
 */
class DeleteStoredPaymentPlugin
{
    /**
     * @var VaultBackupManagement
     */
    private $vaultBackupManagement;

    /**
     * @var DeleteTokenProcessor
     */
    private $tokenUnActivateProcessor;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param VaultBackupManagement $backupManagement
     * @param DeleteTokenProcessor $vaultDeleteProcessor
     * @param MessageManager $messageManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        VaultBackupManagement $backupManagement,
        DeleteTokenProcessor $vaultDeleteProcessor,
        MessageManager $messageManager,
        UrlInterface $urlBuilder
    ) {
        $this->vaultBackupManagement = $backupManagement;
        $this->tokenUnActivateProcessor = $vaultDeleteProcessor;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param PaymentTokenRepositoryInterface $subject
     * @param bool $result
     * @param PaymentTokenInterface $vaultToken
     * @return bool
     */
    public function afterDelete(
        PaymentTokenRepositoryInterface $subject,
        $result,
        PaymentTokenInterface $vaultToken
    ) {
        if ($result) {
            $this->performProcessingByVaultTokenObject($vaultToken);
        }

        return $result;
    }

    /**
     * Deactivate token vault token object
     *
     * @param PaymentTokenInterface $token
     */
    protected function performProcessingByVaultTokenObject($token)
    {
        $gatewayToken = $token->getGatewayToken();
        $profiles = $this->tokenUnActivateProcessor->unActivateTokenAndSuspendRelatedProfiles($gatewayToken);
        $backupedGatewayTokens = $this->vaultBackupManagement->getBackupedGatewayTokens($token);
        foreach ($backupedGatewayTokens as $backupGatewayToken) {
            $profiles += $this->tokenUnActivateProcessor->unActivateTokenAndSuspendRelatedProfiles($backupGatewayToken);
        }

        if ($profiles) {
            $this->createWarningMessage();
        }
    }

    /**
     * Deactivate token vault token value
     *
     * @param string $token
     */
    protected function performProcessingByVaultTokenValue($token)
    {
        $profiles = $this->tokenUnActivateProcessor->unActivateTokenAndSuspendRelatedProfiles($token);

        if ($profiles) {
            $this->createWarningMessage();
        }
    }

    /**
     * Create warning message
     */
    private function createWarningMessage()
    {
        $url = $this->urlBuilder->getUrl('aw_sarp2/profile/index');

        $this->messageManager->addComplexWarningMessage(
            'awSarp2DeleteSavedCardWarningMessage',
            ['url' => $url]
        );
    }
}
