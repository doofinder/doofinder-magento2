<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * ArraySerialized backend model
 *
 * We need custom backend to make sure that serialization method does not change
 * because of the MAGETWO-80296 issue, which forces us to deserialize value manually.
 */
class ArraySerialized extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\Serializer $serializer
     */
    private $serializer;

    /**
     * @param \Doofinder\Feed\Helper\Serializer $serializer
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\Serializer $serializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Unserialize value
     *
     * @return void
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setValue(empty($value) ? false : $this->serializer->unserialize($value));
        }
    }

    /**
     * Serialize value
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            unset($value['__empty']);
            $this->setValue($this->serializer->serialize($value));
        }

        return parent::beforeSave();
    }
}
