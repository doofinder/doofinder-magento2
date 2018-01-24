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
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Doofinder\Feed\Model\Generator\Component::class
    ) {

        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
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
        return $this->objectManager->create($this->instanceName . '\\' . $componentName, $data);
        // @codingStandardsIgnoreEnd
    }
}
