<?php

namespace Doofinder\Feed\Block;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SearchResult
 */
class SearchResult extends Template
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * SearchResult constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     */
    public function __construct(
        Context $context,
        StoreConfig $storeConfig
    ) {
        parent::__construct($context);

        $this->storeConfig = $storeConfig;
    }

    /**
     * Check if search is set to Doofinder.
     *
     * @return boolean
     */
    public function isDoofinderEngineEnabled()
    {
        return $this->storeConfig->isInternalSearchEnabled();
    }

    /**
     * @return string
     */
    public function getItemAnchorClass()
    {
        return SearchResultItem::ANCHOR_ELEMENT_CLASS;
    }

    /**
     * Get ajax url for registering banner click.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('doofinder/feed/registerclick');
    }

    /**
     * Get search result container.
     *
     * @return string
     */
    public function getSearchResultContainer()
    {
        return $this->storeConfig->getSearchResultContainer();
    }

    /**
     * Get search result link.
     *
     * @return string
     */
    public function getSearchResultLink()
    {
        return $this->storeConfig->getSearchResultLink();
    }
}
