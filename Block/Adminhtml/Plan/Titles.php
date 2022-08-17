<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Plan;

use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Framework\Registry;

/**
 * Class Titles
 * @package Aheadworks\Sarp2\Block\Adminhtml\Plan
 */
class Titles extends \Magento\Backend\Block\Template
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::plan/titles.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'aw_sarp2_plan_titles';

    /**
     * @param Context $context
     * @param SystemStore $systemStore
     * @param Registry $coreRegistry
     * @param JsonSerializer $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        SystemStore $systemStore,
        Registry $coreRegistry,
        JsonSerializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->systemStore = $systemStore;
        $this->coreRegistry = $coreRegistry;
        $this->serializer = $serializer;
    }

    /**
     * Get stores options
     *
     * @return array
     */
    public function getStoresOptions()
    {
        return $this->systemStore->getStoreValuesForForm(false, true);
    }

    /**
     * Get storefront titles
     *
     * @return array
     */
    public function getTitles()
    {
        $descriptions = $this->coreRegistry->registry('aw_srp2_plan_titles') ? : [];
        if ($this->isSingleStoreEnabled()) {
            foreach ($descriptions as $key => $description) {
                if ($description[PlanTitleInterface::STORE_ID] != 0) {
                    unset($descriptions[$key]);
                }
            }
        }

        if (count($descriptions) == 0) {
            $descriptions[] = [
                PlanTitleInterface::STORE_ID => 0,
                PlanTitleInterface::TITLE => ''
            ];
        }

        return $descriptions;
    }

    /**
     * Check if single store mode enabled
     *
     * @return bool
     */
    public function isSingleStoreEnabled()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Serialize data to json string
     *
     * @param mixed $data
     * @return bool|false|string
     */
    public function jsonEncode($data)
    {
        return $this->serializer->serialize($data);
    }
}
