<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\CleanerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class GiftCards
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner
 */
class GiftCards implements CleanerInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function clean($quote)
    {
        $quote->setGiftCards(null);
        foreach ($quote->getAllAddresses() as $address) {
            $address->setGiftCards(
                $this->serializer->serialize([])
            );
        }
        return $quote;
    }
}
