<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions;

use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\Generator\Key as KeyGenerator;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Magento\Framework\DataObjectFactory;

/**
 * Class SaveAction
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions
 */
class SaveAction
{
    /**
     * @var OptionResource
     */
    private $resource;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var KeyGenerator
     */
    private $keyGenerator;

    /**
     * @param OptionResource $resource
     * @param DataObjectFactory $dataObjectFactory
     * @param KeyGenerator $keyGenerator
     */
    public function __construct(
        OptionResource $resource,
        DataObjectFactory $dataObjectFactory,
        KeyGenerator $keyGenerator
    ) {
        $this->resource = $resource;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * Save subscription options
     *
     * @param $productId
     * @param $optionRows
     * @param $origOptionRows
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($productId, $optionRows, $origOptionRows)
    {
        $isChanged = false;
        $old = [];
        $new = [];

        if (null === $optionRows) {
            return $isChanged;
        }

        if (!is_array($origOptionRows)) {
            $origOptionRows = [];
        }

        $keySourceFields = [
            SubscriptionOptionInterface::OPTION_ID,
            SubscriptionOptionInterface::WEBSITE_ID
        ];
        foreach ($origOptionRows as $origRow) {
            $key = $this->keyGenerator->generate($origRow, $keySourceFields);
            $old[$key] = $origRow;
        }
        foreach ($optionRows as $row) {
            $key = $this->keyGenerator->generate($row, $keySourceFields);
            $new[$key] = $row;
        }

        $delete = array_diff_key($old, $new);
        $insert = array_diff_key($new, $old);
        $update = array_intersect_key($new, $old);

        if (!empty($delete)) {
            foreach ($delete as $data) {
                $this->resource->deleteOptionsData(
                    $productId,
                    $data['website_id'],
                    isset($data['option_id']) ? $data['option_id'] : null
                );
                $isChanged = true;
            }
        }
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $this->resource->saveOptionsData(
                    $this->dataObjectFactory->create(
                        ['data' => array_merge($data, ['product_id' => $productId])]
                    )
                );
                $isChanged = true;
            }
        }
        if (!empty($update)) {
            foreach ($update as $key => $data) {
                if ($this->keyGenerator->generate($data) != $this->keyGenerator->generate($old[$key])) {
                    $this->resource->saveOptionsData(
                        $this->dataObjectFactory->create(
                            ['data' => array_merge($data, ['product_id' => $productId])]
                        )
                    );
                    $isChanged = true;
                }
            }
        }
        return $isChanged;
    }
}
