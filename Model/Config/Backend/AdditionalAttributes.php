<?php

namespace Doofinder\Feed\Model\Config\Backend;

use \Magento\Framework\Exception\ValidatorException;

/**
 * AdditionalAttribute backend model
 * Validate and correct additional attributes before save
 */
class AdditionalAttributes extends ArraySerialized
{
    /**
     * @var \Doofinder\Feed\Model\AdditionalAttributes\DisallowedAttributesProvider
     */
    private $disallowedAttributes;

    /**
     * AdditionalAttributes constructor.
     * @param \Doofinder\Feed\Model\AdditionalAttributes\DisallowedAttributesProvider $disallowedAttributes
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
        \Doofinder\Feed\Model\AdditionalAttributes\DisallowedAttributesProvider $disallowedAttributes,
        \Doofinder\Feed\Helper\Serializer $serializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->disallowedAttributes = $disallowedAttributes;
        parent::__construct(
            $serializer,
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
     * Prepare data before save.
     *
     * @return \Doofinder\Feed\Model\Config\Backend\ArraySerialized
     * @throws ValidatorException When there's a validation error.
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value) && isset($value['__empty'])) {
            unset($value['__empty']);
        }
        foreach ($value as &$item) {
            $this->validate($item);

            $item['field'] = $this->cleanField($item['field']);

            if (!$item['field']) {
                throw new ValidatorException(__("Additional attribute's name is invalid."));
            }
        }
        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * Transforms given value in such a way it can be used as Field's value.
     *
     * @param string $value
     * @return string
     */
    private function cleanField($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        return str_replace(' ', '_', $value);
    }

    /**
     * @param array $item
     * @return void
     * @throws ValidatorException If field data is invalid.
     */
    private function validate(array $item)
    {
        $validate = is_array($item)
            && isset($item['field'])
            && trim($item['field']);

        if (!$validate) {
            throw new ValidatorException(__("Additional attribute's data is invalid."));
        }

        $disallowed = $this->disallowedAttributes->get();
        if (in_array($item['field'], $disallowed)) {
            throw new ValidatorException(__(
                'Attribute %1 is disallowed. Change the field name',
                $item['field']
            ));
        }
    }
}
