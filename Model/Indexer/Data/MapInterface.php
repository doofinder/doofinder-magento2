<?php

namespace Doofinder\Feed\Model\Indexer\Data;

/**
 * Interface MapInterface
 * The interface for Indexer Mappers
 */
interface MapInterface
{
    /**
     * @param array $documents
     * @param integer $scopeId
     * @return mixed
     */
    public function map(array $documents, $scopeId);
}
