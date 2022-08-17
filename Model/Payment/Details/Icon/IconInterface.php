<?php
namespace Aheadworks\Sarp2\Model\Payment\Details\Icon;

/**
 * Interface IconInterface
 *
 * @package Aheadworks\Sarp2\Model\Payment\Details\Icon
 */
interface IconInterface
{
    /**
     * Retrieve icon url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Retrieve icon width
     *
     * @return int
     */
    public function getWidth();

    /**
     * Retrieve icon height
     *
     * @return int
     */
    public function getHeight();
}
