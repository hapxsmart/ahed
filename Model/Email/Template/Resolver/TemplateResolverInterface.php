<?php
namespace Aheadworks\Sarp2\Model\Email\Template\Resolver;

use Aheadworks\Sarp2\Engine\NotificationInterface;

/**
 * Interface TemplateResolverInterface
 *
 * @package Aheadworks\Sarp2\Model\Email\Template\Resolver
 */
interface TemplateResolverInterface
{
    /**
     * Resolve template id
     *
     * @param NotificationInterface $notification
     * @return string
     */
    public function resolve(NotificationInterface $notification);
}
