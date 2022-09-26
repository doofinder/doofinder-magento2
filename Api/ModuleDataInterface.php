<?php
declare(strict_types=1);

namespace Doofinder\Feed\Api;

interface ModuleDataInterface
{
    /**
     * Obtains the version from the Doofinder module
     * @return string
     */
    public function getVersion(): string;
}
