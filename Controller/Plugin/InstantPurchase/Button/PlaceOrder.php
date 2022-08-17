<?php
namespace Aheadworks\Sarp2\Controller\Plugin\InstantPurchase\Button;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\InstantPurchase\Controller\Button\PlaceOrder as PlaceOrderAction;

/**
 * Class PlaceOrder
 * @package Aheadworks\Sarp2\Controller\Plugin\InstantPurchase\Button
 */
class PlaceOrder
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var IsSubscription
     */
    private $productChecker;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param RequestInterface $request
     * @param Validator $formKeyValidator
     * @param IsSubscription $productChecker
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        RequestInterface $request,
        Validator $formKeyValidator,
        IsSubscription $productChecker,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory
    ) {
        $this->request = $request;
        $this->formKeyValidator = $formKeyValidator;
        $this->productChecker = $productChecker;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @param PlaceOrderAction $subject
     * @param \Closure $proceed
     * @return Json
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(PlaceOrderAction $subject, \Closure $proceed)
    {
        if ($this->formKeyValidator->validate($this->request)) {
            $productId = (int)$this->request->getParam('product');
            if ($productId
                && $this->productChecker->checkById($productId)
                && (int)$this->request->getParam('aw_sarp2_subscription_type', 0) > 0
            ) {
                $warningMessage = __(
                    'We are sorry, subscriptions can\'t be purchased using Instant Purchase. '
                    . 'Instead, use Add To Cart button and then proceed to checkout.'
                );
                $this->messageManager->addWarningMessage($warningMessage);

                /** @var Json $result */
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $result->setData(['response' => $warningMessage]);
                return $result;
            }
        }
        return $proceed();
    }
}
