<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class VaultDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class VaultDataBuilder implements BuilderInterface
{
    /**
     * Add/Update a secure customer vault record
     */
    const CUSTOMER_VAULT = 'customer_vault';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [
            self::CUSTOMER_VAULT => 'add_customer'
        ];

        return $result;
    }
}
