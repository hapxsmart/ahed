<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface;

/**
 * Class VaultConfig
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider
 */
class VaultConfig implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PaymentMethodListInterface
     */
    private $vaultPaymentList;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PaymentMethodListInterface $vaultPaymentList
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PaymentMethodListInterface $vaultPaymentList
    ) {
        $this->storeManager = $storeManager;
        $this->vaultPaymentList = $vaultPaymentList;
    }

    /**
     * Return configuration array
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        $output['vault'] = $this->getVaultData();
        return $output;
    }

    /**
     * Get vault payments set as inactive
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function getVaultData()
    {
        $availableMethods = [];
        $storeId = $this->storeManager->getStore()->getId();
        $vaultPayments = $this->vaultPaymentList->getActiveList($storeId);

        foreach ($vaultPayments as $method) {
            $availableMethods[$method->getCode()] = [
                'is_enabled' => false
            ];
        }

        return $availableMethods;
    }
}
