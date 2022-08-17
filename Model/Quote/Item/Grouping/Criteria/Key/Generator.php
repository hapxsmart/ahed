<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\Key;

use Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\Key;
use Aheadworks\Sarp2\Model\Quote\Item\Grouping\Criteria\KeyFactory;
use Magento\Quote\Model\Quote\Item;

class Generator
{
    /**
     * @var Extractor
     */
    private $extractor;

    /**
     * @var KeyFactory
     */
    private $keyFactory;

    /**
     * @param Extractor $extractor
     * @param KeyFactory $keyFactory
     */
    public function __construct(
        Extractor $extractor,
        KeyFactory $keyFactory
    ) {
        $this->extractor = $extractor;
        $this->keyFactory = $keyFactory;
    }

    /**
     * Generate key
     *
     * @param Item $item
     * @param array $criteria
     * @return Key
     */
    public function generate($item, array $criteria)
    {
        $keyData = $this->extractor->extractKeyData($item, $criteria);

        return $this->keyFactory->create(
            $keyData
        );
    }
}
