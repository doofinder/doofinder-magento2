<?php

namespace Doofinder\Feed\Registry;

/**
 * Class IndexerScope
 * The class responsible for storing information about indexer scope
 */
class IndexerScope
{
    const SCOPE_FULL = 'indexer_execute_full';
    const SCOPE_DELAYED = 'indexer_execute_delayed';

    /**
     * @var string
     */
    private $indexerScope;

    /**
     * @param string $indexerScope
     * @return void
     */
    public function setIndexerScope($indexerScope)
    {
        $this->indexerScope = $indexerScope;
    }

    /**
     * @return string
     */
    public function getIndexerScope()
    {
        return $this->indexerScope;
    }
}
