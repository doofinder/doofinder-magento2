<?php

namespace Doofinder\Feed\Model\Eav\Validator\Attribute;

use Magento\Eav\Model\Validator\Attribute\Code;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Class DoofinderCode
 * The class responsible for checking, if attribute code is not used in Doofinder Index Settings
 */
class DoofinderCode extends Code
{
    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * DoofinderCode constructor.
     * @param StoreConfig $storeConfig
     */
    public function __construct(StoreConfig $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * Check if currently saved attribute is not used in Doofinder Index Settings
     * @param string $attributeCode
     * @return boolean
     */
    public function isValid($attributeCode): bool
    {
        if (!$this->storeConfig->isInternalSearchEnabled()) {
            return parent::isValid($attributeCode);
        }

        $attributes = $this->storeConfig->getDoofinderFields();
        $attributeKeys = array_keys($attributes);
        if (in_array($attributeCode, $attributeKeys)) {
            $this->_addMessages([
                __(
                    'Attribute code %1 is already used in Doofinder Index Settings. '
                    . 'Change attribute code here or in Doofinder',
                    $attributeCode
                )
            ]);
        }

        return parent::isValid($attributeCode);
    }
}
