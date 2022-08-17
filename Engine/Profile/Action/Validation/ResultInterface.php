<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Validation;

/**
 * Interface ResultInterface
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Validation
 */
interface ResultInterface
{
    /**
     * Check is action valid
     *
     * @return bool
     */
    public function isValid();

    /**
     * Get validation message
     *
     * @return string
     */
    public function getMessage();
}
