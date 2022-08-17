<?php
namespace Aheadworks\Sarp2\Gateway\Request;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class BuilderCompositeWithSubjectMerging
 *
 * @package Aheadworks\Sarp2\Gateway\Request
 */
class BuilderCompositeWithSubjectMerging implements BuilderInterface
{
    /**
     * @var BuilderInterface[] | TMap
     */
    private $builders;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $builders
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $builders = []
    ) {
        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => BuilderInterface::class
            ]
        );
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [];
        foreach ($this->builders as $builder) {
            $subject = $buildSubject;
            $subject['buildResult'] = $result;
            $result = $this->merge($result, $builder->build($subject));
        }

        return $result;
    }

    /**
     * Merge function for builders
     *
     * @param array $result
     * @param array $builder
     * @return array
     */
    protected function merge(array $result, array $builder)
    {
        return array_replace_recursive($result, $builder);
    }
}
