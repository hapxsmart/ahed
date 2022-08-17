<?php
namespace Aheadworks\Sarp2\Model\Profile;

/**
 * Class SequenceConfig
 * @package Aheadworks\Sarp2\Model\Profile
 */
class SequenceConfig
{
    /**
     * @var array
     */
    private $defaultValues = [
        'prefix' => '',
        'suffix' => '',
        'startValue' => 1,
        'step' => 1,
        'warningValue' => 4294966295,
        'maxValue' => 4294967295
    ];

    /**
     * Get configuration field
     *
     * @param string|null $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (!array_key_exists($key, $this->defaultValues)) {
            return null;
        }
        return $this->defaultValues[$key];
    }
}
