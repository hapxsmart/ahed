<?php
namespace Aheadworks\Sarp2\Controller\Profile\Guest;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Access\Token\Validator;
use Aheadworks\Sarp2\Model\Access\TokenRepository;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class Extend
 *
 * @package Aheadworks\Sarp2\Controller\Profile\Guest
 */
class Extend extends AbstractProfileActionWithToken
{
    const RESOURCE = 'Profile:Extend';

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var ActionPermission
     */
    protected $actionPermission;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProfileRepositoryInterface $profileRepository
     * @param TokenRepository $tokenRepository
     * @param Validator $tokenValidator
     * @param ProfileManagementInterface $profileManagement
     * @param ActionPermission $actionPermission
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProfileRepositoryInterface $profileRepository,
        TokenRepository $tokenRepository,
        Validator $tokenValidator,
        ProfileManagementInterface $profileManagement,
        ActionPermission $actionPermission
    ) {
        parent::__construct($context, $registry, $profileRepository, $tokenRepository, $tokenValidator);
        $this->profileManagement = $profileManagement;
        $this->actionPermission = $actionPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $profile = $this->getProfile();
            $profileId = $profile->getProfileId();

            if (!$this->actionPermission->isExtendActionAvailable($profileId)) {
                throw new LocalizedException(__(
                    'We are sorry, this subscription can not be extended anymore. Please purchase a new one.'
                ));
            }

            $this->profileManagement->extend($profileId);

            $this->messageManager->addSuccessMessage(__(
                'Your subscription extended for the next %1 payments. Thank you.',
                [$profile->getProfileDefinition()->getTotalBillingCycles()]
            ));
            $resultRedirect->setPath('*/profile_edit/index', ['profile_id' => $profileId]);

            return $resultRedirect;
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while extend the subscription.')
            );
        }
        $resultRedirect->setPath('*/profile/index');

        return $resultRedirect;
    }

    /**
     * Check if action with profile is allowed
     *
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isExtendActionAvailable($profileId);
    }
}
