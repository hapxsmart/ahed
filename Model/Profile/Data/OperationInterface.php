<?php
namespace Aheadworks\Sarp2\Model\Profile\Data;

use Magento\Framework\Exception\LocalizedException;

interface OperationInterface
{
    /**
     * Perform operation over profile data
     *
     * @param int $profileId
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $profileId, array $data);
}
