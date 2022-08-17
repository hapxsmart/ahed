<?php

require 'payment_tokens.php';

use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ResourceConnection $resource */
$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();
/** @var ProfileResource $resourceModel */
$resourceModel = $objectManager->create(ProfileResource::class);
$entityTable = $resourceModel->getTable('aw_sarp2_profile');

$entitiesData = include __DIR__ . '/profiles_data.php';

foreach ($entitiesData as $data) {
    $queryData = $connection->quote($data);
    $connection->query(
        "INSERT INTO {$entityTable} (`profile_id`, `increment_id`, `store_id`, `created_at`, `updated_at`,"
        . " `status`, `plan_id`, `plan_name`, `is_virtual`, `plan_definition_id`, `start_date`, `items_qty`,"
        . " `customer_id`, `customer_tax_class_id`, `customer_group_id`, `customer_email`, `customer_dob`,"
        . " `customer_fullname`, `customer_prefix`, `customer_firstname`, `customer_middlename`, `customer_lastname`,"
        . " `customer_suffix`, `customer_is_guest`, `shipping_method`, `shipping_description`, `global_currency_code`,"
        . " `base_currency_code`, `profile_currency_code`, `base_to_global_rate`, `base_to_profile_rate`,"
        . " `initial_subtotal`, `base_initial_subtotal`, `initial_subtotal_incl_tax`, `base_initial_subtotal_incl_tax`,"
        . " `initial_tax_amount`, `base_initial_tax_amount`, `initial_grand_total`, `base_initial_grand_total`,"
        . " `trial_subtotal`, `base_trial_subtotal`, `trial_subtotal_incl_tax`, `base_trial_subtotal_incl_tax`,"
        . " `trial_tax_amount`, `base_trial_tax_amount`, `trial_shipping_amount`, `base_trial_shipping_amount`,"
        . " `trial_shipping_amount_incl_tax`, `base_trial_shipping_amount_incl_tax`, `trial_shipping_tax_amount`,"
        . " `base_trial_shipping_tax_amount`, `trial_grand_total`, `base_trial_grand_total`, `regular_subtotal`,"
        . " `base_regular_subtotal`, `regular_subtotal_incl_tax`, `base_regular_subtotal_incl_tax`,"
        . " `regular_tax_amount`, `base_regular_tax_amount`, `regular_shipping_amount`,"
        . " `base_regular_shipping_amount`, `regular_shipping_amount_incl_tax`,"
        . " `base_regular_shipping_amount_incl_tax`, `regular_shipping_tax_amount`,"
        . " `base_regular_shipping_tax_amount`, `regular_grand_total`, `base_regular_grand_total`,"
        . " `payment_method`, `payment_token_id`, `last_order_id`, `last_order_date`, `remote_ip`)"
        . " VALUES ({$queryData});"
    );
}
