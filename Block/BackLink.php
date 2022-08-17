<?php
namespace Aheadworks\Sarp2\Block;

use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Class BackLink
 *
 * @method BackLink setRefererUrl(string $refererUrl)
 * @method BackLink setTitle(string $title)
 * @method BackLink setActionClass(string $actionClass)
 * @method string getRefererUrl()
 * @method string getTitle()
 * @method string getActionClass()
 * @method string|null getDisplayIfParamInUrl()
 * @package Aheadworks\Sarp2\Block
 */
class BackLink extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp2::back_link.phtml';

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @param Context $context
     * @param RedirectInterface $redirect
     * @param JsonSerializer $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        RedirectInterface $redirect,
        JsonSerializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->redirect = $redirect;
        $this->serializer = $serializer;
    }

    /**
     * Get back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // The RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->redirect->getRefererUrl();
    }

    /**
     * Check is display button or not
     *
     * @return bool
     */
    public function isDisplay()
    {
        if ($this->getDisplayIfParamInUrl()) {
            return $this->_request->getParam($this->getDisplayIfParamInUrl(), false);
        }

        return true;
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
