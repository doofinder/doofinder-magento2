<?php

namespace Doofinder\Feed\Api;

/**
 * @api
 */
interface ModuleDataInterface
{
    /**
     * Obtains the data from the Doofinder module. This data contains module's version, M2's version and the store structure.
     * 
     * @return string
     */
    public function get();
}
