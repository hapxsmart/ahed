<?php
namespace Aheadworks\Sarp2\PaymentData\Create;

use Magento\Framework\DataObject;

/**
 * Class Result
 * @package Aheadworks\Sarp2\PaymentData\Create
 */
class Result
{
    /**
     * @var string
     */
    private $tokenType;

    /**
     * @var string
     */
    private $gatewayToken;

    /**
     * @var DataObject
     */
    private $additionalData;

    /**
     * @param string $tokenType
     * @param string $gatewayToken
     * @param DataObject|null $additionalData
     */
    public function __construct(
        $tokenType,
        $gatewayToken,
        $additionalData = null
    ) {
        $this->tokenType = $tokenType;
        $this->gatewayToken = $gatewayToken;
        $this->additionalData = $additionalData;
    }

    /**
     * Get token type
     *
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Get gateway token value
     *
     * @return string
     */
    public function getGatewayToken()
    {
        return $this->gatewayToken;
    }

    /**
     * Get additional data
     *
     * @return DataObject|null
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
