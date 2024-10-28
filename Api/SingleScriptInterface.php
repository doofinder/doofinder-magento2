<?php

namespace Doofinder\Feed\Api;

/**
 * @api
 */
interface SingleScriptInterface
{
    /**
     * Replaces the current script by the new single script.
     *
     * @return array
     */
    public function replace();
}
