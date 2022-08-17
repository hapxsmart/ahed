<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeProductItem\Applier\ArrayCopier;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\ItemManagement;
use Aheadworks\Sarp2\Model\Profile\ToQuote;
use Aheadworks\Sarp2\Model\Quote\Item\ToProfileItem;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResourceModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Applier
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ToQuote
     */
    private $profileToQuote;

    /**
     * @var ToProfileItem
     */
    private $quoteItemToProfileItem;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ItemManagement
     */
    private $itemManagement;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var Manager
     */
    private $notificationManager;

    /**
     * @var ArrayCopier
     */
    private $arrayCopier;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var string[]
     */
    private $listOfCopiedProductOptions = [
        'info_buyRequest/aw_sarp2_subscription_type',
        'aw_sarp2_subscription_plan',
        'aw_sarp2_subscription_option',
    ];

    /**
     * @var string[]
     */
    private $listOfCopiedItemFields = [];

    /**
     * @param ResultFactory $validationResultFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param ItemManagement $itemManagement
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Manager $notificationManager
     * @param ToQuote $profileToQuote
     * @param ToProfileItem $quoteItemToProfileItem
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param ArrayCopier $arrayCopier
     * @param CartRepositoryInterface $quoteRepository
     * @param Config $config
     * @param ValidatorComposite $validator
     * @param CurrencyFactory $currencyFactory
     * @param array $listOfCopiedProductOptions
     * @param array $listOfCopiedItemFields
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        ProfileRepositoryInterface $profileRepository,
        ItemManagement $itemManagement,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Manager $notificationManager,
        ToQuote $profileToQuote,
        ToProfileItem $quoteItemToProfileItem,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        ArrayCopier $arrayCopier,
        CartRepositoryInterface $quoteRepository,
        Config $config,
        ValidatorComposite $validator,
        CurrencyFactory $currencyFactory,
        array $listOfCopiedProductOptions = [],
        array $listOfCopiedItemFields = []
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->profileRepository = $profileRepository;
        $this->itemManagement = $itemManagement;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->notificationManager = $notificationManager;
        $this->profileToQuote = $profileToQuote;
        $this->quoteItemToProfileItem = $quoteItemToProfileItem;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->arrayCopier = $arrayCopier;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->validator = $validator;
        $this->currencyFactory = $currencyFactory;
        $this->listOfCopiedProductOptions = array_merge(
            $this->listOfCopiedProductOptions,
            $listOfCopiedProductOptions
        );
        $this->listOfCopiedItemFields = array_merge(
            $this->listOfCopiedItemFields,
            $listOfCopiedItemFields
        );
    }

    /**
     * @inheritDoc
     * @throws CouldNotUpdateProduct
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $itemId = $action->getData()->getItemId();
        $oldProfileItem = $this->itemManagement->getItemFromProfileById($itemId, $profile);
        $isOneTimeOnly = $this->getIsOneTime($action);
        /** @var DataObject $buyRequest */
        $buyRequest = $this->dataObjectFactory->create([
            'data' => $action->getData()->getBuyRequest()
        ]);
        /** @var ProfileResourceModel $resource */
        $resource = $profile->getResource();

        $resource->beginTransaction();

        $product = $this->getProduct($oldProfileItem->getProductId());

        $quote = $this->profileToQuote->convert($profile);
        $quoteItem = $this->addProductToQuote($quote, $product, $buyRequest);

        $forcedCurrency = $this->currencyFactory
            ->create()
            ->load($profile->getProfileCurrencyCode());
        $quote->setForcedCurrency($forcedCurrency);


        $this->quoteRepository->save($quote);

        $newProfileItem = $this->quoteItemToProfileItem->convert($quoteItem);
        $this->copyProductOptions($oldProfileItem, $newProfileItem);
        $this->copyExtendedFields($oldProfileItem, $newProfileItem);
        $this->itemManagement->addItemToProfile($newProfileItem, $profile);

        foreach ($quoteItem->getChildren() as $childQuoteItem) {
            $newChildProfileItem = $this->quoteItemToProfileItem->convert($childQuoteItem, $newProfileItem);
            $this->copyProductOptions($oldProfileItem, $newChildProfileItem);
            $this->itemManagement->addItemToProfile($newChildProfileItem, $profile);
        }

        if ($isOneTimeOnly) {
            $this->itemManagement->replaceWithAnotherItem($oldProfileItem, $newProfileItem, $profile);
        } else {
            $this->itemManagement->deleteItemFromProfile($oldProfileItem, $profile);
            if ($originalItem = $this->itemManagement->getOriginalItemForReplacementItem($oldProfileItem)) {
                $this->itemManagement->deleteItem($originalItem);
            }
        }

        $this->profileRepository->save($profile);
        $this->quoteRepository->delete($quote);
        $this->updatePayments($profile);

        $resource->commit();
    }

    /**
     * Retrieve one-time flag
     *
     * @param ActionInterface $action
     * @return bool
     */
    private function getIsOneTime(ActionInterface $action)
    {
        return $this->config->canOneTimeEditProductItem()
            ? $action->getData()->getIsOneTimeOnly()
            : false;
    }

    /**
     * Copy product options
     *
     * @param ProfileItemInterface $sourceItem
     * @param ProfileItemInterface $targetItem
     */
    private function copyProductOptions($sourceItem, $targetItem)
    {
        /** @noinspection PhpParamsInspection */
        $productOptions = $this->arrayCopier->copyByPath(
            $sourceItem->getProductOptions(),
            $targetItem->getProductOptions(),
            $this->listOfCopiedProductOptions
        );
        $targetItem->setProductOptions($productOptions);
    }

    /**
     * Copy extended fields
     *
     * @param ProfileItemInterface $sourceItem
     * @param ProfileItemInterface $targetItem
     */
    private function copyExtendedFields($sourceItem, $targetItem)
    {
        foreach ($this->listOfCopiedItemFields as $fieldName) {
            $targetItem->setData(
                $fieldName,
                $sourceItem->getData($fieldName)
            );
        }
    }

    /**
     * Add product to quote and return quote items
     *
     * @param Quote $quote
     * @param ProductInterface $product
     * @param DataObject $buyRequest
     * @return Item
     * @throws CouldNotUpdateProduct
     * @throws LocalizedException
     */
    private function addProductToQuote($quote, $product, $buyRequest)
    {
        $quoteItem = $quote->addProduct($product, $buyRequest);
        if (is_string($quoteItem)) {
            throw new CouldNotUpdateProduct(__($quoteItem));
        }

        return $quoteItem;
    }

    /**
     * Retrieve product by id
     *
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $isValid = $this->validator->isValid($profile, $action);

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $this->validator->getMessage();
        }
        return $this->validationResultFactory->create($resultData);
    }

    /**
     * Update payments
     *
     * @param ProfileInterface $profile
     * @throws CouldNotSaveException
     */
    private function updatePayments($profile)
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $this->updatePaymentTotalsFromProfile($payment, $profile);
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
            $this->notificationManager->reschedule(NotificationInterface::TYPE_UPCOMING_BILLING, $payments);
        }
    }

    /**
     * Update payment totals
     *
     * @param PaymentInterface $payment
     * @param ProfileInterface $profile
     */
    private function updatePaymentTotalsFromProfile(PaymentInterface $payment, ProfileInterface $profile)
    {
        $totalScheduled = 0;
        $baseTotalScheduled = 0;

        switch ($payment->getPaymentPeriod()) {
            case PaymentInterface::PERIOD_INITIAL:
                $totalScheduled = $profile->getInitialGrandTotal();
                $baseTotalScheduled = $profile->getBaseInitialGrandTotal();
                break;
            case PaymentInterface::PERIOD_TRIAL:
                $totalScheduled = $profile->getTrialGrandTotal();
                $baseTotalScheduled = $profile->getBaseTrialGrandTotal();
                break;
            case PaymentInterface::PERIOD_REGULAR:
                if ($payment->getType() == PaymentInterface::TYPE_PLANNED) {
                    $totalScheduled = $profile->getRegularGrandTotal();
                    $baseTotalScheduled = $profile->getBaseRegularGrandTotal();
                }
                break;
        }

        $payment
            ->setTotalScheduled($totalScheduled)
            ->setBaseTotalScheduled($baseTotalScheduled);
    }
}
