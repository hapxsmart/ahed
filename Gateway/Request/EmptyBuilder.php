<?php
namespace Aheadworks\Sarp2\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class EmptyBuilder
 *
 * @package Aheadworks\Sarp2\Gateway\Request
 */
class EmptyBuilder implements BuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [];
    }
}
