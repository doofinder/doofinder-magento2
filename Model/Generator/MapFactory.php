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
     * @param \Magento\Framework\DataObject $context
     * @param array $data
     * @return \Doofinder\Feed\Model\Generator\Map
     */
    public function create(\Magento\Framework\DataObject $context, array $data = [])
    {
        $class = $this->_instanceName;

        if (is_a($context, '\Magento\Catalog\Model\Product')) {
            $class .= '\\Product';
            $typeName = ucwords($context->getTypeId());
            if (class_exists($class . '\\' . $typeName)) {
                $class .= '\\' . $typeName;
            }
        }

        return $this->_objectManager->create($class, [
            'context' => $context,
            'data' => $data,
        ]);
    }

}
