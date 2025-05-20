<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\ChangedItem;

class ItemType
{
    public const PRODUCT = 0;
    public const PAGE = 1;
    public const CATEGORY = 2;
    
    public const PRODUCT_INDICE = 'product';
    public const PAGE_INDICE = 'page';
    public const CATEGORY_INDICE = 'category';

    /**
     * Gets an associative array for using multiindexes' indices
     *
     * @return string[]
     */
    public function getList()
    {
        return [
            self::PRODUCT => self::PRODUCT_INDICE,
            self::PAGE => self::PAGE_INDICE,
            self::CATEGORY => self::CATEGORY_INDICE
        ];
    }
}
