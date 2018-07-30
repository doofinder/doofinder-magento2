<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * AdditionalAttribute backend model
 * Validate and correct additional attributes before save
 */
class AdditionalAttributes extends ArraySerialized
{
    /**
     * Prepare data before save.
     * Transform 'field' to lowercase and replace whitespaces with underscore
     * @return \Doofinder\Feed\Model\Config\Backend\ArraySerialized
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        foreach ($value as $key => $item) {
            if (is_array($item) && array_key_exists('field', $item)) {
                $item['field'] = str_replace(' ', '_', strtolower($item['field']));
                $value[$key] = $item;
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
