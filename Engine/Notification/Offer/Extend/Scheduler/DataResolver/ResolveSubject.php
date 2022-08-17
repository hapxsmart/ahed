<?php
namespace Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler\DataResolver;

use Aheadworks\Sarp2\Api\Data\AccessTokenInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class ResolveSubject
 *
 * @package Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler\DataResolver
 */
class ResolveSubject
{
    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var AccessTokenInterface
     */
    private $token;

    /**
     * @param $profile
     * @param null $token
     */
    public function __construct(
        $profile,
        $token = null
    ) {
        $this->profile = $profile;
        $this->token = $token;
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Get access token
     *
     * @return AccessTokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }
}
