<?php
namespace Aheadworks\Sarp2\Model\Customer;

class Checker
{
    /**
     * Check if customer is the registered one
     *
     * @param int|null $customerId
     * @return bool
     */
    public function isRegisteredCustomer($customerId)
    {
        return $customerId > 0;
    }
}
