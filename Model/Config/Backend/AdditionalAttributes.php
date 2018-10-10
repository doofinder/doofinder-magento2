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

        foreach ($value as $key => &$item) {
            if (!$this->validate($item)) {
                throw new ValidatorException(__("Additional attribute's data is invalid."));
            }

            $item['field'] = $this->cleanField($item['field']);

            if (!$item['field']) {
                throw new ValidatorException(__("Additional attribute's name is invalid."));
            }

            $item['label'] = trim($item['label']);
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
     * Validates item values.
     *
     * @param array $item
     * @return boolean
     */
    private function validate(array $item)
    {
        return is_array($item)
            && isset($item['field'])
            && trim($item['field'])
            && isset($item['label'])
            && trim($item['label']);
    }
}
