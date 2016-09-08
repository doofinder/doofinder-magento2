<?php
namespace Doofinder\Feed\Model\Generator;

/**
 * Generic factory class for abstract maps
 */
class MapFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\Doofinder\Feed\Model\Generator\Map'
    ) {

        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param \Doofinder\Feed\Model\Generator\Item $item
     * @param array $data
     * @return \Doofinder\Feed\Model\Generator\Map
     */
    public function create(\Doofinder\Feed\Model\Generator\Item $item, array $data = [])
    {
        $class = $this->_instanceName;
        $context = $item->getContext();

        if (is_a($context, '\Magento\Catalog\Model\Product')) {
            $class .= '\\Product';
            $typeName = ucwords($context->getTypeId());
            if (class_exists($class . '\\' . $typeName)) {
                $class .= '\\' . $typeName;
            }
        }

        return $this->_objectManager->create($class, [
            'item' => $item,
            'data' => $data,
        ]);
    }

}
