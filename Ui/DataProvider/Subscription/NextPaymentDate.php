<?php
namespace Aheadworks\Sarp2\Ui\DataProvider\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class NextPaymentDate extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $collectionFactory
     * @param ProfileManagementInterface $profileManagement
     * @param TimezoneInterface $localeDate
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        ProfileManagementInterface $profileManagement,
        TimezoneInterface $localeDate,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->profileManagement = $profileManagement;
        $this->localeDate = $localeDate;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $profile = $this->dataPersistor->get('aw_sarp2_profile');
        $this->dataPersistor->clear('aw_sarp2_profile');

        $nextPaymentDateInfo = $this->profileManagement->getNextPaymentInfo($profile[ProfileInterface::PROFILE_ID]);
        $nextPaymentDate = $nextPaymentDateInfo->getPaymentDate();
        $nextPaymentDate = $this->localeDate->date(new \DateTime($nextPaymentDate));

        $profile['next_payment_date'] = $nextPaymentDate->format('d/m/Y');
        return [$profile[ProfileInterface::PROFILE_ID] => $profile];
    }
}
