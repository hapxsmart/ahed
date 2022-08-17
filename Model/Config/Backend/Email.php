<?php
namespace Aheadworks\Sarp2\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Class Email
 *
 * @package Aheadworks\Sarp2\Model\Config\Backend
 */
class Email extends Value
{
    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $value = (string)$this->getValue();
        $value = $this->prepareValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare email value
     *
     * @param string $value
     * @return string
     */
    private function prepareValue($value)
    {
        $valueAsArray = explode(',', (string)$value);

        $prepared = [];
        foreach ($valueAsArray as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $prepared[] = $email;
            }
        }

        return implode(',', $prepared);
    }
}
