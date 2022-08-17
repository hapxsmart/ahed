<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile\Merger\Field;

use Aheadworks\Sarp2\Engine\Profile\Merger\Field\ResolverInterface;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Resolver\Factory as ResolverFactory;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Rule;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleFactory;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet\Definition;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\Specification;
use Aheadworks\Sarp2\Engine\Profile\Merger\Field\SpecificationFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\Merger\Field\RuleSet
 */
class RuleSetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RuleSet
     */
    private $ruleSet;

    /**
     * @var Definition|\PHPUnit_Framework_MockObject_MockObject
     */
    private $definitionMock;

    /**
     * @var RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var SpecificationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $specificationFactoryMock;

    /**
     * @var ResolverFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolverFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->definitionMock = $this->createMock(Definition::class);
        $this->ruleFactoryMock = $this->createMock(RuleFactory::class);
        $this->specificationFactoryMock = $this->createMock(SpecificationFactory::class);
        $this->resolverFactoryMock = $this->createMock(ResolverFactory::class);
        $this->ruleSet = $objectManager->getObject(
            RuleSet::class,
            [
                'definition' => $this->definitionMock,
                'ruleFactory' => $this->ruleFactoryMock,
                'specificationFactory' => $this->specificationFactoryMock,
                'resolverFactory' => $this->resolverFactoryMock
            ]
        );
    }

    public function testGetRules()
    {
        $entityType = 'MergedEntityType';
        $rulesData = ['entityFieldName' => ['spec' => Specification::TYPE_SAME]];

        $ruleMock = $this->createMock(Rule::class);
        $specificationMock = $this->createMock(Specification::class);

        $this->definitionMock->expects($this->once())
            ->method('getDefinition')
            ->with($entityType)
            ->willReturn($rulesData);
        $this->specificationFactoryMock->expects($this->once())
            ->method('create')
            ->with(['type' => Specification::TYPE_SAME])
            ->willReturn($specificationMock);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'fieldName' => 'entityFieldName',
                    'specification' => $specificationMock
                ]
            )
            ->willReturn($ruleMock);

        $this->assertEquals([$ruleMock], $this->ruleSet->getRules($entityType));
    }

    public function testGetRulesWithResolver()
    {
        $entityType = 'MergedEntityType';
        $resolverClassName = 'FieldValueResolver';
        $rulesData = [
            'entityFieldName' => [
                'spec' => Specification::TYPE_SAME,
                'resolver' => $resolverClassName
            ]
        ];

        $ruleMock = $this->createMock(Rule::class);
        $specificationMock = $this->createMock(Specification::class);
        $resolverMock = $this->createMock(ResolverInterface::class);

        $this->definitionMock->expects($this->once())
            ->method('getDefinition')
            ->with($entityType)
            ->willReturn($rulesData);
        $this->specificationFactoryMock->expects($this->once())
            ->method('create')
            ->with(['type' => Specification::TYPE_SAME])
            ->willReturn($specificationMock);
        $this->resolverFactoryMock->expects($this->once())
            ->method('create')
            ->with($resolverClassName)
            ->willReturn($resolverMock);
        $this->ruleFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'fieldName' => 'entityFieldName',
                    'specification' => $specificationMock,
                    'resolver' => $resolverMock
                ]
            )
            ->willReturn($ruleMock);

        $this->assertEquals([$ruleMock], $this->ruleSet->getRules($entityType));
    }

    /**
     * @param string $fieldType
     * @param array $rulesData
     * @param array $expectedResult
     * @dataProvider getFieldsDataProvider
     */
    public function testGetFields($fieldType, $rulesData, $expectedResult)
    {
        $entityType = 'MergedEntityType';
        $this->definitionMock->expects($this->once())
            ->method('getDefinition')
            ->with($entityType)
            ->willReturn($rulesData);
        $this->assertEquals($expectedResult, $this->ruleSet->getFields($entityType, $fieldType));
    }

    /**
     * @return array
     */
    public function getFieldsDataProvider()
    {
        return [
            [
                Specification::TYPE_SAME,
                ['fieldSameType' => ['spec' => Specification::TYPE_SAME]],
                ['fieldSameType']
            ],
            [
                Specification::TYPE_RESOLVABLE,
                ['fieldSameType' => ['spec' => Specification::TYPE_SAME]],
                []
            ],
        ];
    }
}
