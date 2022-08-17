<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class StoreDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request
 */
class StoreDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getProfile();

        return [
            'store_id' => $order->getStoreId()
        ];
    }
}
