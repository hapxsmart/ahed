<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\OfflinePaymentRendererInterface;
use Aheadworks\Sarp2\Block\Product\SubscriptionOptions\Renderer\AbstractRenderer;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\TokenRendererInterface;

/**
 * Class PaymentDetails
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View
 */
class PaymentDetails extends Template
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    protected $paymentTokenRepository;

    /**
     * @param Context $context
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * Retrieve payment token from profile
     *
     * @param ProfileInterface $profile
     * @return PaymentTokenInterface|null
     */
    public function getPaymentToken($profile)
    {
        try {
            $paymentToken = $this->paymentTokenRepository->get($profile->getPaymentTokenId());
        } catch (LocalizedException $exception) {
            $paymentToken = null;
        }
        return $paymentToken;
    }

    /**
     * Render payment token
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    public function renderPaymentToken($paymentToken)
    {
        $renderer = $this->getRenderer($paymentToken->getPaymentMethod());
        if ($renderer && $renderer instanceof TokenRendererInterface) {
            return $renderer->render($paymentToken);
        }

        return '';
    }

    /**
     * Render offline payment details
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function renderOfflinePaymentDetails($profile)
    {
        $renderer = $this->getRenderer($profile->getPaymentMethod());
        if ($renderer && $renderer instanceof OfflinePaymentRendererInterface) {
            return $renderer->render($profile->getPaymentMethod());
        }

        return '';
    }

    /**
     * Retrieve token renderer
     *
     * @param string $paymentMethod
     * @return bool|AbstractRenderer
     */
    public function getRenderer($paymentMethod)
    {
        /** @var RendererList $rendererList */
        $rendererList = $this->getChildBlock('renderer.list');
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }

        $rendererName = $this->getNameInLayout() . '.' . $paymentMethod;
        $renderer = $rendererList->getRenderer($rendererName);

        return $renderer;
    }
}
