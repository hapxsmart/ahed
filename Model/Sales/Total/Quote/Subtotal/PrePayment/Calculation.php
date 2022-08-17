<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\DateResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation\Result;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment\Calculation\ResultFactory;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Initial;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Regular;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Trial;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class Calculation
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Subtotal\PrePayment
 */
class Calculation
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var DateResolver
     */
    private $startDateResolver;

    /**
     * @var Initial
     */
    private $initialGroup;

    /**
     * @var Trial
     */
    private $trialGroup;

    /**
     * @var Regular
     */
    private $regularGroup;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param DateResolver $startDateResolver
     * @param Initial $initialGroup
     * @param Trial $trialGroup
     * @param Regular $regularGroup
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        DateResolver $startDateResolver,
        Initial $initialGroup,
        Trial $trialGroup,
        Regular $regularGroup,
        ResultFactory $resultFactory
    ) {
        $this->optionRepository = $optionRepository;
        $this->startDateResolver = $startDateResolver;
        $this->initialGroup = $initialGroup;
        $this->trialGroup = $trialGroup;
        $this->regularGroup = $regularGroup;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Calculate prepayment item price
     *
     * @param ItemInterface $item
     * @param bool $useBaseCurrency
     * @return Result
     */
    public function calculateItemPrice(ItemInterface $item, $useBaseCurrency)
    {
        $amount = 0.0;
        $sumComponents = [];
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $option = $this->optionRepository->get($optionId->getValue());
            $planDefinition = $option->getPlan()->getDefinition();

            if ($planDefinition->getIsInitialFeeEnabled()) {
                $initialFee = $this->initialGroup->getItemPrice($item, $useBaseCurrency);
                $amount += $initialFee;
                $sumComponents[] = 'initial';
            }
            $isImmediateFirstPayment = $this->startDateResolver->isToday(
                $planDefinition->getStartDateType(),
                $planDefinition->getStartDateDayOfMonth()
            );
            if ($isImmediateFirstPayment) {
                $trialPrice = $this->trialGroup->getItemPrice($item, $useBaseCurrency);
                if ($trialPrice >= 0 && $planDefinition->getIsTrialPeriodEnabled()) {
                    $amount += $trialPrice;
                    $sumComponents[] = 'trial';
                } else {
                    $regularPrice = $this->regularGroup->getItemPrice($item, $useBaseCurrency);
                    $amount += $regularPrice;
                    $sumComponents[] = 'regular';
                }
            }
        }
        return $this->resultFactory->create(
            [
                'amount' => $amount,
                'sumComponents' => $sumComponents
            ]
        );
    }
}
