<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\Generator;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;

/**
 * Class Key
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions\Generator
 */
class Key
{
    /**
     * @var int
     */
    private $newItemsCounter = 0;

    /**
     * Get unique option row data key
     *
     * @param array $data
     * @param array|null $fields
     * @param array $update
     * @return string
     */
    public function generate($data, $fields = null, $update = [])
    {
        $keyParts = [];
        if ($fields === null) {
            $fields = [
                SubscriptionOptionInterface::PLAN_ID,
                SubscriptionOptionInterface::INITIAL_FEE,
                SubscriptionOptionInterface::TRIAL_PRICE,
                SubscriptionOptionInterface::REGULAR_PRICE,
                SubscriptionOptionInterface::IS_AUTO_REGULAR_PRICE,
                SubscriptionOptionInterface::IS_AUTO_TRIAL_PRICE,
                SubscriptionOptionInterface::IS_INSTALLMENTS_MODE
            ];
        }

        foreach ($fields as $field) {
            if (isset($update[$field])) {
                $fieldValue = $update[$field];
            } else {
                $fieldValue = isset($data[$field]) ? $data[$field] : '';
            }

            if ($field == SubscriptionOptionInterface::OPTION_ID
                && $fieldValue == ''
            ) {
                $fieldValue = 'new-' . $this->newItemsCounter;
                $this->newItemsCounter++;
            }
            $keyParts[] = $fieldValue;
        }

        return implode('-', $keyParts);
    }
}
