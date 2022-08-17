<?php
namespace Aheadworks\Sarp2\Engine\Profile;

use Magento\Framework\DataObject;

/**
 * Class Action
 * @package Aheadworks\Sarp2\Engine\Profile
 */
class Action implements ActionInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var DataObject
     */
    private $data;

    /**
     * @param string $type
     * @param DataObject $data
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }
}
