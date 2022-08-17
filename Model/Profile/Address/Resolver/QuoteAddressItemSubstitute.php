<?php
namespace Aheadworks\Sarp2\Model\Profile\Address\Resolver;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;

/**
 * Class QuoteAddressItemSubstitute
 *
 * @package Aheadworks\Sarp2\Model\Profile\Address\Resolver
 */
class QuoteAddressItemSubstitute
{
    /**
     * Template for unique id generation
     */
    const UNIQUE_ID_TEMPLATE = "substitute-%s";

    /**
     * Data key for item unique id
     */
    const UNIQUE_ID_DATA_KEY = 'unique_id';

    /**
     * @var int
     */
    private $itemIdCounter = 0;

    /**
     * Retrieve unique id for quote address item substitute
     *
     * @param ProfileItemInterface $item
     * @return string
     */
    public function getItemUniqueId($item)
    {
        $uniqueId = $item->getItemId();
        $earlierUniqueId = $item->getData(self::UNIQUE_ID_DATA_KEY);
        if (empty($uniqueId)) {
            if (empty($earlierUniqueId)) {
                $uniqueId = $this->generateUniqueId();
                $item->setData(self::UNIQUE_ID_DATA_KEY, $uniqueId);
            } else {
                $uniqueId = $earlierUniqueId;
            }
        }
        return $uniqueId;
    }

    /**
     * Retrieve newly generated unique id
     *
     * @return string
     */
    private function generateUniqueId()
    {
        return sprintf(self::UNIQUE_ID_TEMPLATE, ++$this->itemIdCounter);
    }
}
