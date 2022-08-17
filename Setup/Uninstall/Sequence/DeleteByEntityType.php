<?php
namespace Aheadworks\Sarp2\Setup\Uninstall\Sequence;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMetadata;

/**
 * Class DeleteByEntityType
 */
class DeleteByEntityType
{
    /**
     * @var ResourceMetadata
     */
    private $resourceMetadata;

    /**
     * @var MetaFactory
     */
    private $metaFactory;

    /**
     * @var AppResource
     */
    private $appResource;

    /**
     * @param ResourceMetadata $resourceMetadata
     * @param MetaFactory $metaFactory
     * @param AppResource $appResource
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        MetaFactory $metaFactory,
        AppResource $appResource
    ) {
        $this->resourceMetadata = $resourceMetadata;
        $this->metaFactory = $metaFactory;
        $this->appResource = $appResource;
    }

    /**
     * Delete sequences by entity type
     *
     * @param string $entityType
     * @return void
     * @throws \Exception
     */
    public function execute($entityType): void
    {
        $metadataIds = $this->getMetadataIdsByEntityType($entityType);
        $profileIds = $this->getProfileIdsByMetadataIds($metadataIds);

        $this->appResource->getConnection('sales')->delete(
            $this->appResource->getTableName('sales_sequence_profile'),
            ['profile_id IN (?)' => $profileIds]
        );

        foreach ($metadataIds as $metadataId) {
            $metadata = $this->metaFactory->create();
            $this->resourceMetadata->load($metadata, $metadataId);
            if ($metadata->getId()) {
                $this->appResource->getConnection('sales')->dropTable(
                    $metadata->getSequenceTable()
                );
                $this->resourceMetadata->delete($metadata);
            }
        }
    }

    /**
     * Retrieves Metadata Ids by entity type
     *
     * @param string $entityType
     * @return int[]
     */
    private function getMetadataIdsByEntityType($entityType)
    {
        $connection = $this->appResource->getConnection('sales');
        $bind = ['entity_type' => $entityType];
        $select = $connection->select()->from(
            $this->appResource->getTableName('sales_sequence_meta'),
            ['meta_id']
        )->where(
            'entity_type = :entity_type'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Retrieves Profile Ids by metadata ids
     *
     * @param int[] $metadataIds
     * @return int[]
     */
    private function getProfileIdsByMetadataIds(array $metadataIds)
    {
        $connection = $this->appResource->getConnection('sales');
        $select = $connection->select()
            ->from(
                $this->appResource->getTableName('sales_sequence_profile'),
                ['profile_id']
            )->where('meta_id IN (?)', $metadataIds);

        return $connection->fetchCol($select);
    }
}
