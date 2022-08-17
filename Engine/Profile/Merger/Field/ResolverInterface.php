<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field;

/**
 * Interface ResolverInterface
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field
 */
interface ResolverInterface
{
    /**
     * Resolve merging field value
     *
     * @param array $entities
     * @param string $field
     * @return mixed
     */
    public function getResolvedValue($entities, $field);
}
