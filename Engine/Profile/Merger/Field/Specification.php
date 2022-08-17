<?php
namespace Aheadworks\Sarp2\Engine\Profile\Merger\Field;

/**
 * Class Specification
 * @package Aheadworks\Sarp2\Engine\Profile\Merger\Field
 */
class Specification
{
    /**
     * Field types
     */
    const TYPE_SAME = 1;
    const TYPE_SAME_IF_POSSIBLE = 2;
    const TYPE_RESOLVABLE = 3;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get field merging type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
