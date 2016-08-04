<?php
namespace Doofinder\Feed\Model\Generator;

/**
 * Generic factory class for abstract models
 */
abstract class ComponentFactory
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
        $instanceName = '\Doofinder\Feed\Model\Generator\Component'
    ) {

        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @param string $componentName
     * @return \Doofinder\Feed\Model\Generator\Component
     */
    public function create(array $data = array(), $componentName)
    {
        return $this->_objectManager->create($this->_instanceName . '\\' . $componentName, $data);
    }

}
