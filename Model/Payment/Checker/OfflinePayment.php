<?php
namespace Aheadworks\Sarp2\Model\Payment\Checker;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodList;

/**
 * Class OfflinePayment
 *
 * @package Aheadworks\Sarp2\Model\Payment\Checker
 */
class OfflinePayment
{
    /**
     * @var array
     * @deprecated
     */
    private $allowedMethods;

    /**
     * @var IntegratedMethodList
     */
    private $integratedMethodList;

    /**
     * @param IntegratedMethodList $integratedMethodList
     * @param array $allowedMethods
     */
    public function __construct(
        IntegratedMethodList $integratedMethodList,
        array $allowedMethods = []
    ) {
        $this->integratedMethodList = $integratedMethodList;
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Check
     *
     * @param string $methodCode
     * @return bool
     */
    public function check($methodCode)
    {
        $result = in_array($methodCode, $this->allowedMethods);

        if (!$result) {
            $result = $this->checkInList($methodCode);
        }

        return $result;
    }

    /**
     * Check if payment is offline by list
     *
     * @param string $methodCode
     * @return bool
     */
    private function checkInList($methodCode)
    {
        foreach ($this->integratedMethodList->getList() as $integratedMethod) {
            if ($integratedMethod->getCode() == $methodCode
                && $integratedMethod->isOffline()
            ) {
                return true;
            }
        }

        return false;
    }
}
