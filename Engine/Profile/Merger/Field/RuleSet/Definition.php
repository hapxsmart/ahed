<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Address\Fax;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Address\NameAttributes;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Item\ProductOptions;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Item\Qty;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile\CustomerData;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Profile\ShippingMethod;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;

/**
 * Class Definition
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet
 */
class Definition
{
    /**
     * @var array
     */
    private $definitions = [
        ProfileInterface::class => [
            ProfileInterface::STORE_ID => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::CUSTOMER_ID => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::CUSTOMER_IS_GUEST => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::CUSTOMER_TAX_CLASS_ID => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::CUSTOMER_EMAIL => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::CUSTOMER_DOB => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CUSTOMER_FULLNAME => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CUSTOMER_PREFIX => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CUSTOMER_FIRSTNAME => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CUSTOMER_MIDDLENAME => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CUSTOMER_LASTNAME => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CUSTOMER_SUFFIX => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => CustomerData::class
            ],
            ProfileInterface::CHECKOUT_SHIPPING_METHOD => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => ShippingMethod::class
            ],
            ProfileInterface::CHECKOUT_SHIPPING_DESCRIPTION => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => ShippingMethod::class
            ],
            ProfileInterface::GLOBAL_CURRENCY_CODE => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::BASE_CURRENCY_CODE => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::PROFILE_CURRENCY_CODE => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::BASE_TO_GLOBAL_RATE => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::BASE_TO_PROFILE_RATE => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::PAYMENT_METHOD => ['spec' => Specification::TYPE_SAME],
            ProfileInterface::PAYMENT_TOKEN_ID => ['spec' => Specification::TYPE_SAME]
        ],
        ProfileAddressInterface::class => [
            ProfileAddressInterface::CUSTOMER_ADDRESS_ID => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::CUSTOMER_ID => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::REGION_ID => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::FAX => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => Fax::class
            ],
            ProfileAddressInterface::REGION => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::POSTCODE => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::LASTNAME => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::STREET => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::CITY => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::EMAIL => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::TELEPHONE => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::COUNTRY_ID => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::FIRSTNAME => ['spec' => Specification::TYPE_SAME],
            ProfileAddressInterface::PREFIX => [
                'spec' => Specification::TYPE_SAME_IF_POSSIBLE,
                'resolver' => NameAttributes::class
            ],
            ProfileAddressInterface::MIDDLENAME => [
                'spec' => Specification::TYPE_SAME_IF_POSSIBLE,
                'resolver' => NameAttributes::class
            ],
            ProfileAddressInterface::SUFFIX => [
                'spec' => Specification::TYPE_SAME_IF_POSSIBLE,
                'resolver' => NameAttributes::class
            ]
        ],
        ProfileItemInterface::class => [
            ProfileItemInterface::PRODUCT_ID => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::PRODUCT_TYPE => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::STORE_ID => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::IS_VIRTUAL => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::SKU => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::NAME => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::DESCRIPTION => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::WEIGHT => ['spec' => Specification::TYPE_SAME],
            ProfileItemInterface::PRODUCT_OPTIONS => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => ProductOptions::class
            ],
            ProfileItemInterface::QTY => [
                'spec' => Specification::TYPE_RESOLVABLE,
                'resolver' => Qty::class
            ],
        ]
    ];

    /**
     * Get rule set definition for specified entity type
     *
     * @param string $entityType
     * @return array
     */
    public function getDefinition($entityType)
    {
        return isset($this->definitions[$entityType])
            ? $this->definitions[$entityType]
            : [];
    }
}
