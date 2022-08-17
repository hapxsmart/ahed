<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\ResolverInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Set\DataResolver;
use Aheadworks\Sarp2\Model\Profile\Address\Resolver\FullName as FullNameResolver;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CustomerData
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile
 */
class CustomerData implements ResolverInterface
{
    /**
     * @var array
     */
    private $fieldToCustomerGetterMap = [
        ProfileInterface::CUSTOMER_DOB => 'getDob',
        ProfileInterface::CUSTOMER_PREFIX => 'getPrefix',
        ProfileInterface::CUSTOMER_FIRSTNAME => 'getFirstname',
        ProfileInterface::CUSTOMER_MIDDLENAME => 'getMiddlename',
        ProfileInterface::CUSTOMER_LASTNAME => 'getLastname',
        ProfileInterface::CUSTOMER_SUFFIX => 'getSuffix'
    ];

    /**
     * @var array
     */
    private $fieldToAddressGetterMap = [
        ProfileInterface::CUSTOMER_PREFIX => 'getPrefix',
        ProfileInterface::CUSTOMER_FIRSTNAME => 'getFirstname',
        ProfileInterface::CUSTOMER_MIDDLENAME => 'getMiddlename',
        ProfileInterface::CUSTOMER_LASTNAME => 'getLastname',
        ProfileInterface::CUSTOMER_SUFFIX => 'getSuffix'
    ];

    /**
     * @var array
     */
    private $fieldToProfileGetterMap = [
        ProfileInterface::CUSTOMER_PREFIX => 'getCustomerPrefix',
        ProfileInterface::CUSTOMER_FIRSTNAME => 'getCustomerFirstname',
        ProfileInterface::CUSTOMER_MIDDLENAME => 'getCustomerMiddlename',
        ProfileInterface::CUSTOMER_LASTNAME => 'getCustomerLastname',
        ProfileInterface::CUSTOMER_SUFFIX => 'getCustomerSuffix'
    ];

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var DataResolver
     */
    private $setDataResolver;

    /**
     * @var array
     */
    private $resolved = [];

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param FullNameResolver $fullNameResolver
     * @param DataResolver $setDataResolver
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        FullNameResolver $fullNameResolver,
        DataResolver $setDataResolver
    ) {
        $this->customerRepository = $customerRepository;
        $this->fullNameResolver = $fullNameResolver;
        $this->setDataResolver = $setDataResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolvedValue($entities, $field)
    {
        $key = $this->getKey($entities);
        if (!isset($this->resolved[$key])) {
            $this->resolved[$key] = $this->resolve($entities);
        }
        return isset($this->resolved[$key][$field])
            ? $this->resolved[$key][$field]
            : null;
    }

    /**
     * Get cache key
     *
     * @param ProfileInterface[] $entities
     * @return string
     */
    private function getKey($entities)
    {
        /**
         * @param ProfileInterface $profile
         * @return int
         */
        $closure = function ($profile) {
            return $profile->getProfileId();
        };
        $keyParts = array_map($closure, $entities);
        return implode('-', $keyParts);
    }

    /**
     * Resolve customer data
     *
     * @param ProfileInterface[]|object[] $entities
     * @return array
     */
    private function resolve($entities)
    {
        $data = [];

        $customerId = $entities[0]->getCustomerId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            foreach ($this->fieldToCustomerGetterMap as $fieldName => $getter) {
                $data[$fieldName] = $customer->$getter();
            }
        } else {
            $addressType = $this->setDataResolver->isVirtual($entities)
                ? 'billing'
                : 'shipping';

            /** @var object[] $addresses */
            $addresses = $this->setDataResolver->getAddresses($entities, $addressType);
            $data = $this->resolveFromSet($addresses, $this->fieldToAddressGetterMap);
        }

        if (!$this->isCompleted($data)) {
            $data = $this->resolveFromSet($entities, $this->fieldToProfileGetterMap);
        }
        if ($this->isCompleted($data) && !isset($data[ProfileInterface::CUSTOMER_FULLNAME])) {
            $data[ProfileInterface::CUSTOMER_FULLNAME] = $this->fullNameResolver->getFullName(
                $this->convertToProfileAddressData($data)
            );
        }

        return $data;
    }

    /**
     * Convert to profile address data
     *
     * @param array $profileData
     * @return array
     */
    private function convertToProfileAddressData($profileData)
    {
        $result = [];
        $keys = [
            ProfileInterface::CUSTOMER_PREFIX,
            ProfileInterface::CUSTOMER_FIRSTNAME,
            ProfileInterface::CUSTOMER_MIDDLENAME,
            ProfileInterface::CUSTOMER_LASTNAME,
            ProfileInterface::CUSTOMER_SUFFIX
        ];
        foreach ($keys as $key) {
            if (isset($profileData[$key])) {
                $parts = explode('_', (string)$key);
                array_splice($parts, 0, 1);
                $addressDataKey = implode('_', $parts);
                $result[$addressDataKey] = $profileData[$key];
            }
        }
        return $result;
    }

    /**
     * Resolve data by analyze of set of entities
     *
     * @param object[] $entities
     * @param array $map
     * @return array
     */
    private function resolveFromSet($entities, $map)
    {
        $data = [];
        foreach ($map as $fieldName => $getter) {
            if ($this->isActualSame($entities, $getter)) {
                $data[$fieldName] = $entities[0]->$getter();
            }
        }
        if (!$this->isCompleted($data)) {
            $data = $this->findCompleted($entities, $map);
        }
        return $data;
    }

    /**
     * Check if getter results are the same for all entities
     *
     * @param object[] $entities
     * @param string $getter
     * @return bool
     */
    private function isActualSame($entities, $getter)
    {
        $areSame = true;
        $sample = $entities[0]->$getter();

        $entitiesCount = count($entities);
        if ($entitiesCount > 1) {
            for ($index = 1; $index < $entitiesCount; $index++) {
                if ($sample != $entities[$index]->$getter()) {
                    $areSame = false;
                }
            }
        }

        return $areSame;
    }

    /**
     * Check if data may considered completed
     *
     * @param array $data
     * @return bool
     */
    private function isCompleted($data)
    {
        return isset($data[ProfileInterface::CUSTOMER_FIRSTNAME])
            || isset($data[ProfileInterface::CUSTOMER_LASTNAME]);
    }

    /**
     * Find completed data fetched from a single entity
     *
     * @param object[] $entities
     * @param array $map
     * @return array
     */
    private function findCompleted($entities, $map)
    {
        foreach ($entities as $entity) {
            $data = [];
            foreach ($map as $fieldName => $getter) {
                $data[$fieldName] = $entity->$getter();
            }
            if ($this->isCompleted($data)) {
                return $data;
            }
        }
        return [];
    }
}
