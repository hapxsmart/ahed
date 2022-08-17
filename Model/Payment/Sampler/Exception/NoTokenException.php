<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Exception;

use Aheadworks\Sarp2\Model\Payment\Sampler\Info;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class NoTokenException
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Exception
 */
class NoTokenException extends LocalizedException
{
    /**
     * @var Info
     */
    private $samplerInfo;

    /**
     * @inheritDoc
     * @param Phrase $phrase
     * @param Info $samplerInfo
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase, Info $samplerInfo, \Exception $cause = null, $code = 0) {
        parent::__construct($phrase, $cause, $code);
        $this->samplerInfo = $samplerInfo;
    }

    /**
     * @return Info
     */
    public function getSamplerInfo(): Info
    {
        return $this->samplerInfo;
    }
}
