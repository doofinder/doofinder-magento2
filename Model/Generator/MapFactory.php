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
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Doofinder\Feed\Model\Generator\Map::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
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
        $class = $this->instanceName;
        $context = $item->getContext();

        if (is_a($context, \Magento\Catalog\Model\Product::class)) {
            $class .= '\\Product';
            $typeName = ucwords($context->getTypeId());
            if (class_exists($class . '\\' . $typeName)) {
                $class .= '\\' . $typeName;
            }
        }

        // @codingStandardsIgnoreStart
        return $this->objectManager->create($class, [
            'item' => $item,
            'data' => $data,
        ]);
        // @codingStandardsIgnoreEnd
    }
}
