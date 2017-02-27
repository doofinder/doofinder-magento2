<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * Class Password
 * @package Doofinder\Feed\Model\Config\Backend
 */
class Password extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        if (!preg_match('/^[a-zA-Z0-9_-]*$/', $this->getValue())) {
            $config = $this->getFieldConfig();

            throw new \Magento\Framework\Exception\LocalizedException(__(
                '%1 value is invalid. Only alphanumeric characters with underscores (_) and hyphens (-) are allowed.',
                $config['label']
            ));
        }

        return parent::beforeSave();
    }
}
