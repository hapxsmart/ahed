<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Profile\Merger\Resolver;

use Aheadworks\Sarp2\Engine\Profile\Merger\Field\ResolverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class CustomerFirstNameDummy
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Profile\Merger\Resolver
 */
class CustomerFirstNameDummy implements ResolverInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getResolvedValue($entities, $field)
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()
            ->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        return $customer->getFirstname();
    }
}
