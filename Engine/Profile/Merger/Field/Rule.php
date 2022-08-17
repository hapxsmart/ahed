<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field;

/**
 * Class Rule
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field
 */
class Rule
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var Specification
     */
    private $specification;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @param $fieldName
     * @param $specification
     * @param ResolverInterface|null $resolver
     */
    public function __construct(
        $fieldName,
        $specification,
        $resolver = null
    ) {
        $this->fieldName = $fieldName;
        $this->specification = $specification;
        $this->resolver = $resolver;
    }

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get field specification
     *
     * @return Specification
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @return ResolverInterface|null
     */
    public function getResolver()
    {
        return $this->resolver;
    }
}
