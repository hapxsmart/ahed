<?php
namespace Aheadworks\Sarp2\Controller\Profile\Guest;

use Aheadworks\Sarp2\Api\Data\AccessTokenInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Access\Token\Validator;
use Aheadworks\Sarp2\Model\Access\TokenRepository;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;

/**
 * Class AbstractProfileWithToken
 *
 * @package Aheadworks\Sarp2\Controller\Profile\Guest
 */
abstract class AbstractProfileActionWithToken extends Action
{
    /**
     * Resource id
     */
    const RESOURCE = 'AbstractGuestResource';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @var Validator
     */
    private $tokenValidator;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProfileRepositoryInterface $profileRepository
     * @param TokenRepository $tokenRepository
     * @param Validator $tokenValidator
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProfileRepositoryInterface $profileRepository,
        TokenRepository $tokenRepository,
        Validator $tokenValidator
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->tokenRepository = $tokenRepository;
        $this->tokenValidator = $tokenValidator;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        $this->getRequest()->setParams(['ajax' => 1]);
        $token = null;

        try {
            try {
                $token = $this->tokenRepository->getByValue(
                    $this->getRequest()->getParam('token')
                );
            } catch (\Exception $exception) {
                throw new Exception('The link you clicked is no longer available.');
            }

            if (!$this->isAllowResource($token)) {
                throw new Exception('Resource not available');
            }

            if (!$this->tokenValidator->isValid($token)) {
                throw new Exception($this->tokenValidator->getMessage());
            }

            $this->registry->register('profileId', $token->getProfileId());
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('');
        } finally {
            if ($token instanceof AccessTokenInterface) {
                $this->invalidateToken($token);
            }
        }

        $result = parent::dispatch($request);

        return $result;
    }

    /**
     * Check if this resource is allowed for token
     *
     * @param AccessTokenInterface $token
     * @return bool
     */
    protected function isAllowResource(AccessTokenInterface $token)
    {
        if ($token->getAllowedResource()
            && $token->getAllowedResource() != static::RESOURCE
        ) {
            return false;
        }

        return true;
    }

    /**
     * Invalidate access token
     *
     * @param AccessTokenInterface $token
     */
    protected function invalidateToken(AccessTokenInterface $token)
    {
        try {
            $this->tokenRepository->delete($token);
        } catch (Exception $exception) {
        }
    }

    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProfile()
    {
        try {
            $profileId = $this->registry->registry('profileId');
            $profile = $this->profileRepository->get($profileId);
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $profile;
    }
}
