<?php
namespace Aheadworks\Sarp2\Model\Email;

/**
 * Class Template
 *
 * @package Aheadworks\Sarp2\Model\Email
 */
class Template extends \Magento\Email\Model\Template
{
    /**
     * @inheritDoc
     */
    public function load($modelId, $field = null)
    {
        parent::load($modelId, $field);
        $this->setData('is_legacy', true);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function loadDefault($templateId)
    {
        parent::loadDefault($templateId);
        $this->setData('is_legacy', true);

        return $this;
    }
}
