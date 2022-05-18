<?php
declare(strict_types=1);


namespace Doofinder\Feed\Api\Data;

interface MapInterface
{
    /**
     * @param array $documents
     * @param integer $scopeId
     * @return mixed
     */
    public function map(array $documents, int $scopeId);
}
