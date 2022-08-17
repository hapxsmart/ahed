<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Profile\Merger;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\EntityMerger;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Rule;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;
use Aheadworks\Sarp2\Test\Integration\Engine\Profile\Merger\Resolver\CustomerFirstNameDummy;
use Aheadworks\Sarp2\Test\Integration\Engine\Profile\Merger\Resolver\CustomerPrefixDummy;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class EntityMergerTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Profile\Merger
 */
class EntityMergerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EntityMerger
     */
    private $entityMerger;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->entityMerger = $this->objectManager->create(EntityMerger::class);
    }

    /**
     * @param array $source1Update
     * @param array $source2Update
     * @param array $expected
     * @dataProvider mergeDataProvider
     */
    public function testMerge($source1Update, $source2Update, $expected)
    {
        /** @var ProfileInterface $source1 */
        $source1 = $this->objectManager->create(ProfileInterface::class);
        $source1->setCustomerId(1)
            ->setCustomerFirstname(null);
        foreach ($source1Update as $method => $value) {
            $source1->$method($value);
        }
        /** @var ProfileInterface $source2 */
        $source2 = $this->objectManager->create(ProfileInterface::class);
        $source1->setCustomerId(1)
            ->setCustomerFirstname(null);
        foreach ($source2Update as $method => $value) {
            $source2->$method($value);
        }

        $customerIdRule = $this->objectManager->create(
            Rule::class,
            [
                'fieldName' => 'customer_id',
                'specification' => $this->objectManager->create(
                    Specification::class,
                    ['type' => Specification::TYPE_SAME]
                )
            ]
        );
        $customerFirstNameRule = $this->objectManager->create(
            Rule::class,
            [
                'fieldName' => 'customer_firstname',
                'specification' => $this->objectManager->create(
                    Specification::class,
                    ['type' => Specification::TYPE_RESOLVABLE]
                ),
                'resolver' => $this->objectManager->create(CustomerFirstNameDummy::class)
            ]
        );
        $customerPrefixRule = $this->objectManager->create(
            Rule::class,
            [
                'fieldName' => 'customer_prefix',
                'specification' => $this->objectManager->create(
                    Specification::class,
                    ['type' => Specification::TYPE_SAME_IF_POSSIBLE]
                ),
                'resolver' => $this->objectManager->create(CustomerPrefixDummy::class)
            ]
        );

        $actual = $this->entityMerger->merge(
            $this->objectManager->create(ProfileInterface::class),
            [$source1, $source2],
            [
                $customerIdRule,
                $customerFirstNameRule,
                $customerPrefixRule
            ],
            ProfileInterface::class
        );
        $this->assertEquals(1, $actual->getCustomerId());
        $this->assertEquals('John', $actual->getCustomerFirstname());
        foreach ($expected as $method => $value) {
            $this->assertEquals($value, $actual->$method());
        }
    }

    /**
     * @return array
     */
    public function mergeDataProvider()
    {
        return [
            [
                ['setCustomerPrefix' => 'Pref.'],
                ['setCustomerPrefix' => 'Pref.'],
                ['getCustomerPrefix' => 'Pref.']
            ],
            [
                ['setCustomerPrefix' => 'Pref.'],
                [],
                ['getCustomerPrefix' => 'Mr.']
            ]
        ];
    }
}
