<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Address extends Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/edit/address.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'aw_sarp2_edit_address';

    /**
     * @param Template\Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProfileRepositoryInterface $profileRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
    }

    /**
     * Get edit customer address url
     *
     * @return string
     * @throws LocalizedException
     */
    public function getEditCustomerAddressUrl()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        $profile = $this->profileRepository->get($profileId);
        return $this->getUrl('customer/index/edit', ['id' => $profile->getCustomerId()]);
    }
}
