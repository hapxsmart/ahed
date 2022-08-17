<?php
namespace Aheadworks\Sarp2\Engine\Notification\Locator;

use Aheadworks\Sarp2\Engine\NotificationInterface;

/**
 * Class Registry
 * @package Aheadworks\Sarp2\Engine\Notification\Locator
 */
class Registry
{
    /**
     * @var NotificationInterface|null
     */
    private $notification = null;

    /**
     * Set notification instance to registry
     *
     * @param NotificationInterface $notification
     * @return void
     */
    public function register($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get registered notification instance
     *
     * @return NotificationInterface
     */
    public function get()
    {
        return $this->notification;
    }

    /**
     * Unset registered notification instance
     */
    public function unRegister()
    {
        $this->notification = null;
    }
}
