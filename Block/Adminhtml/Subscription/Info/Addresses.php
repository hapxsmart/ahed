<?php
namespace Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Model\Profile\Address\Resolver\FullName as FullNameResolver;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\CountryFactory;
use Magento\Payment\Api\PaymentMethodListInterface;
use Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info\Customer\Name
    as CustomerNameInfoBlock;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class Addresses
 * @package Aheadworks\Sarp2\Block\Adminhtml\Subscription\Info
 */
class Addresses extends Template
{
    /**
     * @var FullNameResolver
     */
    private $fullNameResolver;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::subscription/info/addresses.phtml';

    /**
     * @param Context $context
     * @param FullNameResolver $fullNameResolver
     * @param CountryFactory $countryFactory
     * @param PaymentMethodListInterface $paymentMethodList
     * @param array $data
     */
    public function __construct(
        Context $context,
        FullNameResolver $fullNameResolver,
        CountryFactory $countryFactory,
        PaymentMethodListInterface $paymentMethodList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fullNameResolver = $fullNameResolver;
        $this->countryFactory = $countryFactory;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * todo: M2SARP-382
     * Get full name
     *
     * @param ProfileAddressInterface $address
     * @return string
     */
    public function getFullName($address)
    {
        return $this->fullNameResolver->getFullName($address);
    }

    /**
     * Get country name
     *
     * @param string $countryId
     * @return string
     */
    public function getCountryName($countryId)
    {
        $country = $this->countryFactory->create()->loadByCode($countryId);
        return $country->getName();
    }

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getPaymentMethodTitle()
    {
        $profile = $this->getProfile();
        $methods = $this->paymentMethodList->getList($profile->getStoreId());
        foreach ($methods as $method) {
            if ($method->getCode() == $profile->getPaymentMethod()) {
                return $method->getTitle();
            }
        }
        return '';
    }

    /**
     * Retrieve block to render customer name
     *
     * @return bool|BlockInterface
     */
    public function getCustomerNameInfoBlock()
    {
        $customerNameInfoBlockName = 'customer.name.info.block';

        try {
            if ($this->getLayout()->hasElement($customerNameInfoBlockName)) {
                return $this->getLayout()->getBlock($customerNameInfoBlockName);
            }

            return $this->getLayout()->addBlock(
                CustomerNameInfoBlock::class,
                $customerNameInfoBlockName,
                $this->getNameInLayout()
            );
        } catch (\Exception $exception) {
            $this->_logger->warning($exception->getMessage());
            return false;
        }
    }

    /**
     * Retrieve customer name html output
     *
     * @param int|null $customerId
     * @param string $customerFullName
     * @return string
     */
    public function getCustomerNameHtml($customerId, $customerFullName)
    {
        $customerNameHtml = '';
        $customerNameInfoBlock = $this->getCustomerNameInfoBlock();
        if ($customerNameInfoBlock instanceof BlockInterface) {
            $customerNameInfoBlock
                ->setData(
                    CustomerNameInfoBlock::CUSTOMER_ID_DATA_KEY,
                    $customerId
                )->setData(
                    CustomerNameInfoBlock::CUSTOMER_FULL_NAME_DATA_KEY,
                    $customerFullName
                )
            ;
            $customerNameHtml = $customerNameInfoBlock->toHtml();
        }

        return $customerNameHtml;
    }

    /**
     * Get edit address url
     *
     * @return string
     */
    public function getEditAddressUrl()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/subscription_edit/address',
            [ProfileInterface::PROFILE_ID => $profileId]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
