<?php
namespace Aheadworks\Sarp2\Controller;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Block\BackLink;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class AbstractAction
 * @package Aheadworks\Sarp2\Controller
 */
abstract class AbstractAction extends Action
{
    /**
     * @var ProfileRepositoryInterface
     */
    protected $profileRepository;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository
    ) {
        parent::__construct($context);
        $this->profileRepository = $profileRepository;
    }

    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     * @throws NotFoundException
     */
    abstract protected function getProfile();

    /**
     * Retrieve referer url
     *
     * @return string
     */
    abstract protected function getRefererUrl();

    /**
     * Set url to back link
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return $this
     */
    protected function setUrlToBackLink($resultPage)
    {
        $linkBack = $this->linkBackBlock($resultPage);
        if ($linkBack) {
            $linkBack->setRefererUrl($this->getRefererUrl());
        }
        return $this;
    }

    /**
     * Set title to back link
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param string $title
     * @return $this
     */
    protected function setTitleToBackLink($resultPage, $title)
    {
        $linkBack = $this->linkBackBlock($resultPage);
        if ($linkBack) {
            $linkBack->setTitle($title);
        }
        return $this;
    }

    /**
     * Set action class to back link
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param string $actionClass
     * @return $this
     */
    protected function setActionClassToBackLink($resultPage, $actionClass)
    {
        $linkBack = $this->linkBackBlock($resultPage);
        if ($linkBack) {
            $linkBack->setActionClass($actionClass);
        }
        return $this;
    }

    /**
     * Retrieve link back block
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return BackLink
     */
    protected function linkBackBlock($resultPage)
    {
        return $resultPage->getLayout()->getBlock('customer.account.link.back');
    }
}
