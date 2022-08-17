<?php
namespace Aheadworks\Sarp2\Model\Quote\Plugin;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner\Pool;
use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detector;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Class QuoteRepository
 * @package Aheadworks\Sarp2\Model\Quote\Plugin
 */
class QuoteRepository
{
    /**
     * @var Detector
     */
    private $invalidDataDetector;

    /**
     * @var Pool
     */
    private $invalidDataCleanerPool;

    /**
     * @param Detector $invalidDataDetector
     * @param Pool $invalidDataCleanerPool
     */
    public function __construct(
        Detector $invalidDataDetector,
        Pool $invalidDataCleanerPool
    ) {
        $this->invalidDataDetector = $invalidDataDetector;
        $this->invalidDataCleanerPool = $invalidDataCleanerPool;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param \Closure $proceed
     * @param CartInterface|Quote $quote
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(CartRepositoryInterface $subject, \Closure $proceed, $quote)
    {
        $detectResult = $this->invalidDataDetector->detect($quote);
        $isInvalid = $detectResult->isInvalid();
        if ($isInvalid) {
            $cleaner = $this->invalidDataCleanerPool->getCleaner($detectResult->getReason());
            $cleaner->clean($quote);
            $quote->collectTotals();
        }
        $proceed($quote);
        if ($isInvalid) {
            throw new LocalizedException(__($detectResult->getErrorMessage()));
        }
    }
}
