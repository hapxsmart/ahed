<?php
namespace Aheadworks\Sarp2\Model\Payment\Details\Icon;

/**
 * Class Icon
 *
 * @package Aheadworks\Sarp2\Model\Payment\Details\Icon
 */
class Icon implements IconInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * Icon constructor.
     *
     * @param string $url
     * @param int $width
     * @param int $height
     */
    public function __construct(string $url, int $width, int $height)
    {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Retrieve icon url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Retrieve icon width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Retrieve icon height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}
