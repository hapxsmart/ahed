<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate\Validator;

use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Aheadworks\Sarp2\Helper\Validator\DateValidator;
use Aheadworks\Sarp2\Model\Config;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class NextPaymentDate
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate\Validator
 */
class NextPaymentDate extends AbstractValidator
{
    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var DateValidator
     */
    private $dateValidator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param PaymentsList $paymentsList
     * @param DateValidator $dateValidator
     * @param Config $config
     */
    public function __construct(
        PaymentsList $paymentsList,
        DateValidator $dateValidator,
        Config $config
    ) {
        $this->paymentsList = $paymentsList;
        $this->dateValidator = $dateValidator;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    protected function performValidation($profile, $action)
    {
        $newNextPaymentDate = $action->getData()->getNewNextPaymentDate();

        if ($this->dateValidator->isValid($newNextPaymentDate, DateTime::DATETIME_PHP_FORMAT)) {
            $newNextPaymentDate = new \DateTime($newNextPaymentDate);
            $newNextPaymentDate->setTime(0, 0, 0);
            $earliestNextPaymentDate = new \DateTime('now');
            $earliestNextPaymentDate->setTime(0, 0, 0);
            $earliestNextPaymentDate
                ->modify('+' . $this->config->getEarliestNextPaymentDate($profile->getStoreId()) . 'days');

            $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
            foreach ($payments as $payment) {
                if ($newNextPaymentDate < $earliestNextPaymentDate) {
                    $this->addMessages(['Next Payment Date must be in the future.']);
                }

                if ($payment->getType() == PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
                    $this->addMessages(['Next Payment date cannot be changed after all payments are done.']);
                }
            }
        } else {
            $this->addMessages(['Next Payment Date is incorrect.']);
        }
    }
}
