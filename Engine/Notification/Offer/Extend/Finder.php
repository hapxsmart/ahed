<?php
namespace Aheadworks\Sarp2\Engine\Notification\Offer\Extend;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface as Definition;
use Aheadworks\Sarp2\Api\Data\ProfileInterface as Profile;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface as Schedule;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;

/**
 * Class Finder
 *
 * @package Aheadworks\Sarp2\Engine\Notification\Offer\Extend
 */
class Finder
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get profile ready for send offer for today
     *
     * @return Profile[]
     */
    public function getReadyForSendOfferForToday()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->join(
                ['definition' => 'aw_sarp2_profile_definition'],
                sprintf(
                    'main_table.%s = definition.%s',
                    Profile::PROFILE_DEFINITION_ID,
                    Definition::DEFINITION_ID
                ),
                []
            )
            ->join(
                ['schedule' => 'aw_sarp2_core_schedule'],
                sprintf(
                    'main_table.%s = schedule.%s',
                    Profile::PROFILE_ID,
                    Schedule::PROFILE_ID
                ),
                []
            )
            ->addFieldToFilter(
                Profile::STATUS,
                ['in' => [
                    Status::ACTIVE,
                    Status::EXPIRED
                ]]
            )
            ->addFieldToFilter(Definition::IS_EXTEND_ENABLE, ['neq' => 0])
            ->addFieldToFilter(Definition::TOTAL_BILLING_CYCLES, ['gt' => 0])
            ->getSelect()
                ->where(
                    sprintf(
                        'CAST(`schedule`.`%s` AS SIGNED) - CAST(`schedule`.`%s` AS SIGNED) IN (1, 0)',
                        Schedule::REGULAR_TOTAL_COUNT,
                        Schedule::REGULAR_COUNT
                    )
                );

        return $collection->getItems();
    }
}
