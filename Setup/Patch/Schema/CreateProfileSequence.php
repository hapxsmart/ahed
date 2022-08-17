<?php
namespace Aheadworks\Sarp2\Setup\Patch\Schema;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\SalesSequence\Model\Builder as SequenceBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\SequenceConfig;

class CreateProfileSequence implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @var SequenceBuilder
     */
    private $sequenceBuilder;

    /**
     * @param StoreManagerInterface $storeManager
     * @param SequenceConfig $sequenceConfig
     * @param SequenceBuilder $sequenceBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SequenceConfig $sequenceConfig,
        SequenceBuilder $sequenceBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->sequenceConfig = $sequenceConfig;
        $this->sequenceBuilder = $sequenceBuilder;
    }

    /**
     * Create profile sequence
     *
     * @throws AlreadyExistsException
     */
    public function apply()
    {
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($this->sequenceConfig->get('startValue'))
                ->setStoreId($store->getId())
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType(Profile::ENTITY)
                ->create();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }
}
