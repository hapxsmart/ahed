<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class DynamicRows
 * @package Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions
 */
class DynamicRows extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Sarp2::product/subscription_options/dynamic-rows.phtml';

    /**
     * @var \WebsiteOptionsRenderer
     */
    private $websiteOptionsRenderer;

    /**
     * @var \PlanOptionsRenderer
     */
    private $planOptionsRenderer;

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'website_id',
            [
                'label' => __('Website'),
                'renderer' => $this->getWebsiteOptionsRenderer()
            ]
        );
        $this->addColumn(
            'plan_id',
            [
                'label' => __('Plan'),
                'renderer' => $this->getPlanOptionsRenderer()
            ]
        );
        $this->addColumn(
            'initial_fee',
            [
                'label' => __('Initial Fee'),
                'class' => '_required required-entry',
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $startTimeOptionsRenderer = $this->getWebsiteOptionsRenderer();
        $endTimeOptionsRenderer = $this->getPlanOptionsRenderer();
        $row->setData(
            'option_extra_attrs',
            [
                'option_' . $startTimeOptionsRenderer->calcOptionHash($row->getStartTime()) => 'selected="selected"',
                'option_' . $endTimeOptionsRenderer->calcOptionHash($row->getEndTime()) => 'selected="selected"'
            ]
        );
    }

    /**
     * Get website options renderer
     *
     * @return \WebsiteOptionsRenderer
     */
    private function getWebsiteOptionsRenderer()
    {
        if (!$this->websiteOptionsRenderer) {
            $this->websiteOptionsRenderer = $this->getLayout()->createBlock(
                \WebsiteOptionsRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->websiteOptionsRenderer;
    }

    /**
     * Get plan options renderer
     *
     * @return \PlanOptionsRenderer
     */
    private function getPlanOptionsRenderer()
    {
        if (!$this->planOptionsRenderer) {
            $this->planOptionsRenderer = $this->getLayout()->createBlock(
                \PlanOptionsRenderer::class,
                '',
                ['data' =>
                    [
                        'is_render_to_js_template' => true,
                        'extra_params' => 'data-role="data-plan-select"'
                            . 'data-mage-init=&quot;{'
                            . '"awSarp2PlanSelect": {'
                            . '"planSelectSelector": "#<%- _id %> [data-role=data-plan-select]",'
                            . '"initialFeeInputSelector": "#<%- _id %>_initial_fee"'
                            . '}'
                            . '}&quot;'
                    ]
                ]
            );
        }
        return $this->planOptionsRenderer;
    }
}
