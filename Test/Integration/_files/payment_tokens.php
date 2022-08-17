<?php

use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ResourceConnection $resource */
$resource = $objectManager->get(ResourceConnection::class);
$connection = $resource->getConnection();
/** @var TokenResource $resourceModel */
$resourceModel = $objectManager->create(TokenResource::class);
$entityTable = $resourceModel->getTable('aw_sarp2_payment_token');

$entitiesData = include __DIR__ . '/payment_tokens_data.php';

foreach ($entitiesData as $data) {
    $queryData = $connection->quote($data);
    $connection->query(
        "INSERT INTO {$entityTable} (`token_id`, `payment_method`, `type`, `token_value`, `created_at`,"
        . " `expires_at`, `is_active`)"
        . " VALUES ({$queryData});"
    );
}
