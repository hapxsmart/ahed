<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Customer;

use Magento\Backend\Block\Template;
use Aheadworks\Sarp2\Model\Customer\Checker as CustomerChecker;
use Magento\Backend\Block\Template\Context;

class Name extends Template
{
    /**#@+
     * Constants defined for keys of the block data
     */
    const CUSTOMER_ID_DATA_KEY          = 'customer_id';
    const CUSTOMER_FULL_NAME_DATA_KEY   = 'customer_full_name';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info/customer/name.phtml';

    /**
     * @var CustomerChecker
     */
    private $customerChecker;

    /**
     * @param Context $context
     * @param CustomerChecker $customerChecker
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerChecker $customerChecker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerChecker = $customerChecker;
    }

    /**
     * Check if the link to the customer profile is available
     *
     * @param int|null $customerId
     * @return bool
     */
    public function isLinkAvailable($customerId)
    {
        return $this->customerChecker->isRegisteredCustomer($customerId);
    }

    /**
     * Generate link to the customer profile in the backend
     *
     * @param int $customerId
     * @return string
     */
    public function getCustomerLink($customerId)
    {
        return $this->_urlBuilder->getUrl(
            'customer/index/edit',
            [
                'id' => $customerId,
            ]
        );
    }
}
