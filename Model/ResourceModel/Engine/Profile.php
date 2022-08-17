<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Engine;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile as ProfileModel;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Profile
 * @package Aheadworks\Sarp2\Model\ResourceModel\Engine
 */
class Profile extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_profile', 'profile_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(ProfileInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Update profile status
     *
     * @param ProfileModel $profile
     * @return void
     */
    public function updateStatus($profile)
    {
        $profileId = $profile->getProfileId();
        if ($profileId) {
            $table = $this->getMainTable();
            $this->getConnection()
                ->update(
                    $table,
                    ['status' => $profile->getStatus()],
                    ['profile_id = ?' => $profileId]
                );
        }
    }

    /**
     * Update profile membership active until date
     *
     * @param ProfileInterface $profile
     * @return void
     */
    public function updateMembershipActiveUntilDate($profile)
    {
        $profileId = $profile->getProfileId();
        if ($profileId) {
            $table = $this->getMainTable();
            $this->getConnection()
                ->update(
                    $table,
                    [ProfileInterface::MEMBERSHIP_ACTIVE_UNTIL_DATE => $profile->getMembershipActiveUntilDate()],
                    ['profile_id = ?' => $profileId]
                );
        }
    }
}
