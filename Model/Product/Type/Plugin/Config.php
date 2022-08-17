<?php
namespace Aheadworks\Sarp2\Model\Product\Type\Plugin;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;

/**
 * Class Config
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin
 */
class Config
{
    /**
     * Supported flag product type config custom attribute code
     */
    const SUPPORTED_CUSTOM_ATTR_CODE = 'aw_sarp2_is_allow_subscriptions';

    /**
     * @var array
     */
    private $supportedProductTypes = [
        Type::TYPE_SIMPLE,
        Type::TYPE_VIRTUAL,
        Configurable::TYPE_CODE,
        DownloadableType::TYPE_DOWNLOADABLE,
        Bundle::TYPE_CODE
    ];

    /**
     * @param ConfigInterface $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAll(ConfigInterface $subject, array $data)
    {
        foreach ($data as $typeCode => &$config) {
            if (!isset($config['custom_attributes'])) {
                $config['custom_attributes'] = [];
            }
            $config['custom_attributes'][self::SUPPORTED_CUSTOM_ATTR_CODE] = in_array(
                $typeCode,
                $this->supportedProductTypes
            );
        }
        return $data;
    }
}
