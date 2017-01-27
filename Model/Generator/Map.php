<?php

namespace Doofinder\Feed\Model\Generator;

class Map extends \Magento\Framework\DataObject
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    protected $_item;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_context;

    /**
     * Class constructor
     *
     * @param \Doofinder\Feed\Model\Generator\Item $context
     * @param array $data = []
     */
    public function __construct(
        \Doofinder\Feed\Model\Generator\Item $item,
        array $data = []
    ) {
        $this->_item = $item;
        $this->_context = $this->_item->getContext();
        parent::__construct($data);
    }

    /**
     * Get value
     *
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        return $this->_context->getData($field);
    }

    /**
     * Before map
     */
    public function before()
    {
    }

    /**
     * After map
     */
    public function after()
    {
    }
}
