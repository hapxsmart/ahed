<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Processor;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface as Payment;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderOptionsProcessor
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $subscriptionOptionRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param SubscriptionOptionRepositoryInterface $subscriptionOptionRepository
     * @param PlanRepositoryInterface $planRepository
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $subscriptionOptionRepository,
        PlanRepositoryInterface $planRepository,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->subscriptionOptionRepository = $subscriptionOptionRepository;
        $this->planRepository = $planRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Process order options
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    public function process($product, &$options)
    {
        $option = $product->getCustomOption('aw_sarp2_subscription_type');
        if ($option) {
            try {
                $subscriptionOption = $this->getSubscriptionOption($option->getValue());
                $options['aw_sarp2_subscription_option'] = $this->getSubscriptionOptionDataArray($option);
                $options['aw_sarp2_subscription_plan'] = $this->getPlanDataArray($subscriptionOption->getPlanId());
                $options['aw_sarp2_subscription_payment_period'] = Payment::PERIOD_INITIAL;
            } catch (LocalizedException $e) {
            }
        }

        return $options;
    }

    /**
     * Retrieve subscription option by option id
     *
     * @param int $optionId
     * @return SubscriptionOptionInterface
     * @throws NoSuchEntityException
     */
    private function getSubscriptionOption($optionId)
    {
        return $this->subscriptionOptionRepository->get($optionId);
    }

    /**
     * Retrieve subscription option data array by option id
     *
     * @param OptionInterface $option
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSubscriptionOptionDataArray($option)
    {
        $optionId = $option->getValue();
        $quoteItem = $option->getItem();
        $option = $this->subscriptionOptionRepository->get($optionId);
        $optionArray = $this->dataObjectProcessor->buildOutputDataArray(
            $option,
            SubscriptionOptionInterface::class
        );
        $optionArray = array_merge($optionArray, [
            'initial_fee' => $quoteItem->getAwSarpInitialFee(),
            'trial_price' => $quoteItem->getAwSarpTrialPrice(),
            'regular_price' => $quoteItem->getAwSarpRegularPrice(),
            'currency_code' => $quoteItem->getQuote()->getQuoteCurrencyCode()
        ]);
        unset($optionArray[SubscriptionOptionInterface::PLAN]);
        unset($optionArray[SubscriptionOptionInterface::PRODUCT]);

        return $optionArray;
    }

    /**
     * Retrieve subscription plan data array by plan id
     *
     * @param int $planId
     * @return array
     * @throws LocalizedException
     */
    private function getPlanDataArray($planId)
    {
        $plan = $this->planRepository->get($planId);

        return $this->dataObjectProcessor->buildOutputDataArray(
            $plan,
            PlanInterface::class
        );
    }
}
