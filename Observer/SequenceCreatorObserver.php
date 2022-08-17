<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\SequenceConfig;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesSequence\Model\Builder;

/**
 * Class SequenceCreatorObserver
 * @package Aheadworks\Sarp2\Observer
 */
class SequenceCreatorObserver implements ObserverInterface
{
    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     */
    private $sequenceConfig;

    /**
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     */
    public function __construct(
        Builder $sequenceBuilder,
        SequenceConfig $sequenceConfig
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $observer->getData('store')->getId();
        $this->sequenceBuilder->setPrefix($storeId)
            ->setSuffix($this->sequenceConfig->get('suffix'))
            ->setStartValue($this->sequenceConfig->get('startValue'))
            ->setStoreId($storeId)
            ->setStep($this->sequenceConfig->get('step'))
            ->setWarningValue($this->sequenceConfig->get('warningValue'))
            ->setMaxValue($this->sequenceConfig->get('maxValue'))
            ->setEntityType(Profile::ENTITY)
            ->create();
        return $this;
    }
}
