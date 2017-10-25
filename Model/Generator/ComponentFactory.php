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
    private $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $_instanceName = null;

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
    public function create(array $data = [], $componentName = '')
    {
        // @codingStandardsIgnoreStart
        return $this->_objectManager->create($this->_instanceName . '\\' . $componentName, $data);
        // @codingStandardsIgnoreEnd
    }
}
