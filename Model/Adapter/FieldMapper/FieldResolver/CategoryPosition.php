<?php

namespace Doofinder\Feed\Model\Adapter\FieldMapper\FieldResolver;

/**
 * Class CategoryPosition
 * The class responsible for retrieving category position name based on Category ID
 */
class CategoryPosition
{
    const ATTR_NAME = 'category_position_';

    /**
     * @param integer $categoryId
     * @return string
     */
    public function getFiledName($categoryId)
    {
        return self::ATTR_NAME . $categoryId;
    }
}
