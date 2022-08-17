<?php
namespace Aheadworks\Sarp2\Model\Plan\Source;

use Magento\Config\Model\Config\Source\Email\Template;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class EmailTemplate
 *
 * @package Aheadworks\Sarp2\Model\Plan\Source
 */
class EmailTemplate implements ArrayInterface
{
    /**
     * @var Template
     */
    private $template;

    /**
     * @param Template $template
     */
    public function __construct(
        Template $template
    ) {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->template
            ->setPath('aw_sarp2_email_settings_offer_extend_subscription_email_template')
            ->toOptionArray();
    }
}
