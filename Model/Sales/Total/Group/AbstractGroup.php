<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Group;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

/**
 * Class AbstractGroup
 * @package Aheadworks\Sarp2\Model\Sales\Total\Group
 */
abstract class AbstractGroup implements GroupInterface
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var SubscriptionPriceCalculatorInterface
     */
    protected $priceCalculator;

    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var PopulatorFactory
     */
    private $populatorFactory;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var array
     */
    private $populateMaps = [];

    /**
     * @var PopulatorInterface[]
     */
    private $populatorInstances = [];

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SubscriptionPriceCalculatorInterface $priceCalculation
     * @param PriceCurrency $priceCurrency
     * @param PopulatorFactory $populatorFactory
     * @param ProviderInterface $provider
     * @param array $populateMaps
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SubscriptionPriceCalculatorInterface $priceCalculation,
        PriceCurrency $priceCurrency,
        PopulatorFactory $populatorFactory,
        ProviderInterface $provider,
        array $populateMaps = []
    ) {
        $this->optionRepository = $optionRepository;
        $this->priceCalculator = $priceCalculation;
        $this->priceCurrency = $priceCurrency;
        $this->populatorFactory = $populatorFactory;
        $this->provider = $provider;
        $this->populateMaps = array_merge($this->populateMaps, $populateMaps);
    }

    /**
     * {@inheritdoc}
     */
    public function getPopulator($entityType)
    {
        if (!isset($this->populateMaps[$entityType])) {
            throw new \InvalidArgumentException('Invalid entity type.');
        }
        if (!isset($this->populatorInstances[$entityType])) {
            $this->populatorInstances[$entityType] = $this->populatorFactory->create(
                ['map' => $this->populateMaps[$entityType]]
            );
        }
        return $this->populatorInstances[$entityType];
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
