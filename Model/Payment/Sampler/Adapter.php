<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler;

use Aheadworks\Sarp2\Model\Payment\Sampler\Adapter\ToOrderPaymentInfoConverter;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data\PaymentDataObjectFactory as SamplerPaymentDataObjectFactory;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader as Reader;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Amount as InfoAmount;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Payment\SamplerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory as MagentoPaymentDataObjectFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\PaymentInterface as QuotePaymentInfoInterface;
use RuntimeException;

/**
 * Class Adapter
 * @package Aheadworks\Sarp2\Model\Payment\Sampler
 */
class Adapter implements SamplerInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    private $paymentMethodCode;

    /**
     * @var InfoAmount
     */
    private $infoAmount;

    /**
     * @var string
     */
    private $placeAction;

    /**
     * @var string
     */
    private $revertAction;

    /**
     * @var SamplerPaymentDataObjectFactory
     */
    private $samplerPaymentDataObjectFactory;

    /**
     * @var MagentoPaymentDataObjectFactory
     */
    private $magentoPaymentDataObjectFactory;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var ToOrderPaymentInfoConverter
     */
    private $toOrderPaymentInfo;

    /**
     * @var bool
     */
    private $useMagentoInterfacesForCommandSubject;

    /**
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param SamplerPaymentDataObjectFactory $samplerPaymentDataObjectFactory
     * @param MagentoPaymentDataObjectFactory $magentoPaymentDataObjectFactory
     * @param string $paymentMethodCode
     * @param InfoAmount $infoAmount
     * @param EventManagerInterface $eventManager
     * @param ToOrderPaymentInfoConverter $toOrderPaymentInfo
     * @param CommandPoolInterface|null $commandPool
     * @param string $placeAction
     * @param string $revertAction
     * @param bool $useMagentoInterfacesForCommandSubject
     */
    public function __construct(
        ValueHandlerPoolInterface $valueHandlerPool,
        SamplerPaymentDataObjectFactory $samplerPaymentDataObjectFactory,
        MagentoPaymentDataObjectFactory $magentoPaymentDataObjectFactory,
        $paymentMethodCode,
        InfoAmount $infoAmount,
        EventManagerInterface $eventManager,
        ToOrderPaymentInfoConverter $toOrderPaymentInfo,
        CommandPoolInterface $commandPool = null,
        $placeAction = 'authorize',
        $revertAction = 'void',
        $useMagentoInterfacesForCommandSubject = false
    ) {
        $this->valueHandlerPool = $valueHandlerPool;
        $this->samplerPaymentDataObjectFactory = $samplerPaymentDataObjectFactory;
        $this->magentoPaymentDataObjectFactory = $magentoPaymentDataObjectFactory;
        $this->paymentMethodCode = $paymentMethodCode;
        $this->commandPool = $commandPool;
        $this->placeAction = $placeAction;
        $this->revertAction = $revertAction;
        $this->infoAmount = $infoAmount;
        $this->eventManager = $eventManager;
        $this->toOrderPaymentInfo = $toOrderPaymentInfo;
        $this->useMagentoInterfacesForCommandSubject = $useMagentoInterfacesForCommandSubject;
    }

    /**
     * {@inheritdoc}
     */
    public function assignData(SamplerInfoInterface $samplerPaymentInfo, DataObject $data)
    {
        $samplerPaymentInfo->setAmount($this->infoAmount->getAmount());
        $samplerPaymentInfo->getMethodInstance()
            ->assignData($data)
            ->validate();

        return $samplerPaymentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function place(SamplerInfoInterface $samplerPaymentInfo, QuotePaymentInfoInterface $quotePaymentInfo)
    {
        $this->setStore($samplerPaymentInfo->getStoreId());
        $this->assertIsMethodActive($samplerPaymentInfo)
            ->assertCanUseForCurrency($samplerPaymentInfo, $samplerPaymentInfo->getBaseCurrencyCode())
            ->assertCanPerformAction($this->placeAction)
            ->assertCanPerformAction($this->revertAction);

        $this->eventManager->dispatch(
            'aw_sarp2_sampler_place_command_before_' . $samplerPaymentInfo->getMethod(),
            [
                'payment' => $samplerPaymentInfo,
            ]
        );

        $orderPaymentInfo = null;
        $arguments = [
            Reader::AMOUNT => $this->infoAmount->getAmount(),
            Reader::COMMAND => $this->placeAction
        ];
        if ($this->useMagentoInterfacesForCommandSubject) {
            $orderPaymentInfo = $this->toOrderPaymentInfo->convertFromQuotePaymentInfo(
                $quotePaymentInfo,
                $samplerPaymentInfo->getProfile()
            );
            $arguments[Reader::PAYMENT] = $this->magentoPaymentDataObjectFactory->create(
                $orderPaymentInfo
            );
            $arguments[Reader::SAMPLER_PAYMENT] = $this->samplerPaymentDataObjectFactory->create(
                $samplerPaymentInfo
            );
        } else {
            $arguments[Reader::PAYMENT] = $this->samplerPaymentDataObjectFactory->create(
                $samplerPaymentInfo
            );
        }

        $this->executeCommand($this->placeAction, $arguments);

        $samplerPaymentInfo->setAmountPlaced($samplerPaymentInfo->getAmount());
        $samplerPaymentInfo->setStatus(SamplerInfoInterface::STATUS_PLACED);

        if ($this->useMagentoInterfacesForCommandSubject) {
            if ($orderPaymentInfo) {
                $this->copyAdditionalInformation($orderPaymentInfo, $samplerPaymentInfo);
            }
        } else {
            $this->copyAdditionalInformation($samplerPaymentInfo, $quotePaymentInfo);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function revert(SamplerInfoInterface $samplerPaymentInfo)
    {
        $orderPaymentInfo = null;
        $arguments = [
            Reader::AMOUNT => $this->infoAmount->getAmount(),
            Reader::COMMAND => $this->revertAction
        ];
        if ($this->useMagentoInterfacesForCommandSubject) {
            $orderPaymentInfo = $this->toOrderPaymentInfo->convertFromSamplerPaymentInfo(
                $samplerPaymentInfo,
                $samplerPaymentInfo->getProfile()
            );
            $arguments[Reader::PAYMENT] = $this->magentoPaymentDataObjectFactory->create(
                $orderPaymentInfo
            );
            $arguments[Reader::SAMPLER_PAYMENT] = $this->samplerPaymentDataObjectFactory->create(
                $samplerPaymentInfo
            );
        } else {
            $arguments[Reader::PAYMENT] = $this->samplerPaymentDataObjectFactory->create(
                $samplerPaymentInfo
            );
        }

        $this->executeCommand($this->revertAction, $arguments);

        $samplerPaymentInfo->setAmountReverted($samplerPaymentInfo->getAmountPlaced());
        $samplerPaymentInfo->setStatus(SamplerInfoInterface::STATUS_RESOLVED);

        if ($this->useMagentoInterfacesForCommandSubject && $orderPaymentInfo) {
            $this->copyAdditionalInformation($orderPaymentInfo, $samplerPaymentInfo);
        }

        return $this;
    }

    /**
     * Merge additional information
     *
     * @param InfoInterface $fromPaymentInfo
     * @param InfoInterface $toPaymentInfo
     */
    private function copyAdditionalInformation(InfoInterface $fromPaymentInfo, InfoInterface $toPaymentInfo)
    {
        foreach ($fromPaymentInfo->getAdditionalInformation() as $key => $value) {
            if (!$toPaymentInfo->hasAdditionalInformation($key)) {
                $toPaymentInfo->setAdditionalInformation($key, $value);
            }
        }
    }

    /**
     * Is active
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)$this->getConfiguredValue('active', $storeId);
    }

    /**
     * Check authorize availability
     *
     * @return bool
     */
    public function canAuthorize()
    {
        return $this->canPerformCommand('authorize');
    }

    /**
     * Check void command availability
     *
     * @return bool
     */
    public function canVoid()
    {
        return $this->canPerformCommand('void');
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return void
     */
    public function setStore($storeId)
    {
        $this->storeId = (int)$storeId;
    }

    /**
     * Get store id
     *
     * @return int
     */
    public function getStore()
    {
        return $this->storeId;
    }

    /**
     * Whether payment command is supported and can be executed
     *
     * @param string $commandCode
     * @return bool
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function canPerformCommand($commandCode)
    {
        return (bool)$this->getConfiguredValue('can_' . $commandCode);
    }

    /**
     * Unifies configured value handling logic
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);
        $subject = [
            'field' => $field
        ];

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }

    /**
     * Execute command
     *
     * @param string $commandCode
     * @param array $arguments
     * @return \Magento\Payment\Gateway\Command\ResultInterface|null
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    private function executeCommand($commandCode, array $arguments = [])
    {
        $command = $this->commandPool->get($commandCode);

        return $command->execute($arguments);
    }

    /**
     * Assert is payment method available
     *
     * @param SamplerInfoInterface $info
     * @return $this
     * @throws RuntimeException
     */
    private function assertIsMethodActive(SamplerInfoInterface $info)
    {
        if (!$this->isActive($info->getStoreId())) {
            throw new RuntimeException('Payment method ' . $info->getMethod() . ' isn\'t active.');
        }
        return $this;
    }

    /**
     * Assert is payment method can be used for specified currency
     *
     * @param SamplerInfoInterface $info
     * @param string $currencyCode
     * @return $this
     * @throws RuntimeException
     */
    private function assertCanUseForCurrency(SamplerInfoInterface $info, $currencyCode)
    {
        if (!$info->getMethodInstance()->canUseForCurrency($info->getStoreId())) {
            throw new RuntimeException(
                'Payment method ' . $info->getMethod() . ' cannot used for currency ' . $currencyCode . '.'
            );
        }
        return $this;
    }

    /**
     * Assert is payment action can be performed
     *
     * @param string $action
     * @return $this
     * @throws RuntimeException
     */
    private function assertCanPerformAction($action)
    {
        $methodName = 'can' . ucwords($action);
        $methodInstance = $this;
        if (!method_exists($methodInstance, $methodName)
            || !$methodInstance->$methodName()
        ) {
            throw new RuntimeException('Payment action ' . $action . ' cannot been performed.');
        }
        return $this;
    }
}
