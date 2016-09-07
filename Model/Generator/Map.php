<?php

namespace Doofinder\Feed\Model\Generator;

class Map extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_context;

    /**
     * Class constructor
     *
     * @param \Magento\Catalog\Model\Product $context
     * @param array $data = []
     */
    public function __construct(
        \Magento\Framework\DataObject $context,
        array $data = []
    ) {
        $this->_context = $context;
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
}
