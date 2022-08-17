<?php
namespace Aheadworks\Sarp2\Cron;

use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Processor as ExtendSubscriptionProcessor;

/**
 * Class ProcessOffers
 *
 * @package Aheadworks\Sarp2\Cron
 */
class ProcessOffers
{
    /**
     * @var ExtendSubscriptionProcessor
     */
    private $extendSubscriptionProcessor;

    /**
     * @param ExtendSubscriptionProcessor $processor
     */
    public function __construct(ExtendSubscriptionProcessor $processor)
    {
        $this->extendSubscriptionProcessor = $processor;
    }

    /**
     * Perform processing of offers
     *
     * @return void
     */
    public function execute()
    {
        $this->extendSubscriptionProcessor->processProfileWithOfferForToday();
    }
}
