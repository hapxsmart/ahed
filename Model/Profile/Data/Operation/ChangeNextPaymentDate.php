<?php
namespace Aheadworks\Sarp2\Model\Profile\Data\Operation;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use Aheadworks\Sarp2\Model\Profile\Data\OperationInterface;

class ChangeNextPaymentDate implements OperationInterface
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var FormatConverter
     */
    private $formatConverter;

    /**
     * @param ProfileManagementInterface $profileManagement
     * @param FormatConverter $formatConverter
     */
    public function __construct(
        ProfileManagementInterface $profileManagement,
        FormatConverter $formatConverter
    ) {
        $this->profileManagement = $profileManagement;
        $this->formatConverter = $formatConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(int $profileId, array $data)
    {
        $nextPaymentDate = $data['next_payment_date'];
        $newNextPaymentDate = $this->formatConverter->reformat($nextPaymentDate, 'd/m/Y');
        $this->profileManagement->changeNextPaymentDate($profileId, $newNextPaymentDate);
    }
}
