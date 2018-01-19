<?php

namespace Doofinder\Feed\Model\Generator\Map\Product;

use \Doofinder\Feed\Model\Generator\Map\Product;

/**
 * Associate product map
 */
class Associate extends Product
{
    /**
     * Get value
     *
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        switch ($field) {
            case 'url_key':
                if ($this->context->isVisibleInSiteVisibility()) {
                    break;
                }
                // nobreak;

            case 'df_id':
            case 'name':
            case 'description':
            case 'price':
            case 'image':
            case 'type_id':
                return;
        }

        return parent::get($field);
    }
}
