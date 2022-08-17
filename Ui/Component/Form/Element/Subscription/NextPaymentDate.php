<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Element\Subscription;

use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\Field;

class NextPaymentDate extends Field
{
    /**
     * Prepare component configuration
     *
     * @return void
     * @throws LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        $config['options']['dateFormat'] = 'dd/M/Y';
        $this->setData('config', $config);
    }
}
