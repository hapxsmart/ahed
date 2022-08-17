<?php
namespace Aheadworks\Sarp2\Ui\DataProvider\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Address extends AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $address = $this->collection->getFirstItem()->getShippingAddress()->getData();
        return [$address[ProfileInterface::PROFILE_ID] => $address];
    }
}
