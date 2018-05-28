<?php

namespace Doofinder\Feed\Plugin\Indexer\Model;

/**
 * Class Indexer
 */
class Indexer
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * Indexer constructor.
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(\Doofinder\Feed\Helper\StoreConfig $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * When Doofinder Search Engine is enabled
     * change catalogsearch_fulltext to Update on Save after first full reindex
     * @param \Magento\Indexer\Model\Indexer $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterReindexAll(\Magento\Indexer\Model\Indexer $subject, $result)
    {
        $indexerId = $subject->getId();
        $isScheduled = $subject->isScheduled();
        $isDoofinderEngine = $this->storeConfig->isInternalSearchEnabled();
        if ($indexerId === \Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID
            && $isDoofinderEngine
            && $isScheduled
        ) {
            $subject->setScheduled(false);
        }
        return $result;
    }
}
