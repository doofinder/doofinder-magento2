<?php

namespace Doofinder\Feed\Model\Generator;

/**
 * Map
 */
class Map extends \Magento\Framework\DataObject
{
    // @codingStandardsIgnoreStart
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    protected $item;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $context;
    // @codingStandardsIgnoreEnd

    /**
     * Class constructor
     *
     * @param \Doofinder\Feed\Model\Generator\Item $item
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Model\Generator\Item $item,
        array $data = []
    ) {
        $this->item = $item;
        $this->context = $this->item->getContext();
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
        return $this->context->getData($field);
    }

    /**
     * Before map
     *
     * @return null
     */
    public function before()
    {
        return null;
    }

    /**
     * After map
     *
     * @return null
     */
    public function after()
    {
        return null;
    }
}
