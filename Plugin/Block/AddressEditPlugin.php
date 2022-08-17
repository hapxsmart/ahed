<?php
namespace Aheadworks\Sarp2\Plugin\Block;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\Address;
use Magento\Customer\Block\Address\Edit;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

/**
 * Class AddressEditPlugin
 * @package Aheadworks\Sarp2\Plugin\Block
 */
class AddressEditPlugin
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(UrlInterface $urlBuilder, RequestInterface $request)
    {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * Change save address url
     *
     * @param Edit $subject
     * @param string $result
     * @return string
     */
    public function afterGetSaveUrl($subject, $result)
    {
        if ($profileId = $this->request->getParam(Address::REDIRECT_TO_SARP2_PROFILE)) {
            $result = $this->urlBuilder->getUrl(
                'customer/address/formPost',
                ['_secure' => true, Address::REDIRECT_TO_SARP2_PROFILE => $profileId]
            );
        }
        return $result;
    }
}
