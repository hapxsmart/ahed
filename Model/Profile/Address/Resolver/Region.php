<?php
namespace Aheadworks\Sarp2\Model\Profile\Address\Resolver;

use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;

/**
 * todo: eliminate, M2SARP-382
 * Class Region
 * @package Aheadworks\Sarp2\Model\Profile\Address\Resolver
 */
class Region
{
    /**
     * @var array
     */
    private $regionInstancesById = [];

    /**
     * @var array
     */
    private $regionInstancesByCode = [];

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var RegionResource
     */
    private $regionResource;

    /**
     * @param RegionFactory $regionFactory
     * @param RegionResource $regionResource
     */
    public function __construct(
        RegionFactory $regionFactory,
        RegionResource $regionResource
    ) {
        $this->regionFactory = $regionFactory;
        $this->regionResource = $regionResource;
    }

    /**
     * Get region code
     *
     * @param int $regionId
     * @param int|string $region
     * @param int $countryId
     * @return string
     */
    public function getRegionCode($regionId, $region, $countryId)
    {
        $regionCode = '';
        if (!$regionId && is_numeric($region)) {
            $regionInstance = $this->getRegionInstanceById($region);
            if ($regionInstance->getCountryId() == $countryId) {
                $regionCode = $regionInstance->getCode();
            }
        } elseif ($regionId) {
            $regionInstance = $this->getRegionInstanceById($regionId);
            if ($regionInstance->getCountryId() == $countryId) {
                $regionCode = $regionInstance->getCode();
            }
        } elseif (is_string($region)) {
            return $region;
        }
        return $regionCode;
    }

    /**
     * Get region ID
     *
     * @param string $regionCode
     * @param int $countryId
     * @return int
     */
    public function getRegionId($regionCode, $countryId)
    {
        return $this->getRegionInstanceByCode($regionCode, $countryId)->getId();
    }

    /**
     * Get region name
     *
     * @param string $regionId
     * @param string $region
     * @param int $countryId
     * @return string
     */
    public function getRegion($regionId, $region, $countryId)
    {
        $regionName = $region;
        if (!$regionId && is_numeric($region)) {
            $regionInstance = $this->getRegionInstanceById($region);
            if ($regionInstance->getCountryId() == $countryId) {
                $regionName = $regionInstance->getName();
            }
        } elseif ($regionId) {
            $regionInstance = $this->getRegionInstanceById($regionId);
            if ($regionInstance->getCountryId() == $countryId) {
                $regionName = $regionInstance->getName();
            }
        }
        return $regionName;
    }

    /**
     * Get region name by code
     *
     * @param string $regionCode
     * @param int $countryId
     * @return string
     */
    public function getRegionByCode($regionCode, $countryId)
    {
        return $this->getRegionInstanceByCode($regionCode, $countryId)->getName();
    }

    /**
     * Get region instance by ID
     *
     * @param int $regionId
     * @return \Magento\Directory\Model\Region
     */
    private function getRegionInstanceById($regionId)
    {
        if (!isset($this->regionInstancesById[$regionId])) {
            $region = $this->regionFactory->create();
            $this->regionResource->load($region, $regionId);
            $this->regionInstancesById[$regionId] = $region;
        }
        return $this->regionInstancesById[$regionId];
    }

    /**
     * Get region instance by code
     *
     * @param string $regionCode
     * @param int $countryId
     * @return \Magento\Directory\Model\Region
     */
    public function getRegionInstanceByCode($regionCode, $countryId)
    {
        $key = $regionCode . '-' . $countryId;
        if (!isset($this->regionInstancesByCode[$key])) {
            $region = $this->regionFactory->create();
            $this->regionResource->loadByCode($region, $regionCode, $countryId);
            $this->regionInstancesByCode[$key] = $region;
        }
        return $this->regionInstancesByCode[$key];
    }
}
