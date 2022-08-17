<?php
namespace Aheadworks\Sarp2\Model\SalesRule\Rule\Condition;

use Aheadworks\Sarp2\Model\Profile\Address as ProfileAddress;
use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\Address as MagentoAddress;

class Address extends MagentoAddress
{
    /**
     * Validate Address Rule Condition
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if ($model instanceof ProfileAddress) {
            if (!$model->hasData($this->getAttribute())) {
                $model->setData($this->getAttribute(), 0);
            }
            $attributeValue = $model->getData($this->getAttribute());

            $isValid = $this->validateAttribute($attributeValue);
        } else {
            $isValid = parent::validate($model);
        }

        return $isValid;
    }
}
