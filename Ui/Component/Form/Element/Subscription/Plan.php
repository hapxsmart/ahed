<?php
namespace Aheadworks\Sarp2\Ui\Component\Form\Element\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Backend as SubscriptionOptionSource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\Form\Field;

class Plan extends Field
{
    /**
     * @var SubscriptionOptionSource
     */
    private $optionSource;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var array
     */
    private $optionsArray = [];

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SubscriptionOptionSource $optionSource
     * @param ProfileRepositoryInterface $profileRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SubscriptionOptionSource $optionSource,
        ProfileRepositoryInterface $profileRepository,
        array $components = [],
        array $data = []
    ) {
        $this->optionSource = $optionSource;
        $this->profileRepository = $profileRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function prepare()
    {
        $profileId = $this->getContext()->getRequestParam(ProfileInterface::PROFILE_ID);
        $profile = $this->profileRepository->get($profileId);
        $config = $this->getData('config');
        $options = $this->getSubscriptionOptions($profile);
        $config['options'] = $options;
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * Get subscription option array
     *
     * @param ProfileInterface $profile
     * @return array
     * @throws LocalizedException
     */
    private function getSubscriptionOptions(ProfileInterface $profile)
    {
        if (empty($this->optionsArray)) {
            $optionsArray = [];
            foreach ($profile->getItems() as $profileItem) {
                $optionsArray = array_replace(
                    $this->optionSource->getPlanOptionArray($profileItem->getProductId()),
                    $optionsArray
                );
            }
            foreach ($optionsArray as $planId => $label) {
                $this->optionsArray[] = ['value' => $planId, 'label' => $label];
            }
        }

        return $this->optionsArray;
    }
}
