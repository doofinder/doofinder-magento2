<?php

namespace Doofinder\Feed\Model\Tax\Plugin;

/**
 * @class Config
 */
class Config
{
    /**
     * Force price conversion
     *
     * @return boolean
     */
    public function aroundNeedPriceConversion()
    {
        return true;
    }
}
