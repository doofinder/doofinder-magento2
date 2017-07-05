<?php

namespace Doofinder\Feed\Plugin;

/**
 * @class Config
 */
class Config
{
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $_indexer;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\Indexer $indexer
     */
    public function __construct(
        \Doofinder\Feed\Helper\Indexer $indexer
    ) {
        $this->_indexer = $indexer;
    }

    /**
     * Store doofinder section config
     *
     * This plugins allows to store doofinder section config
     * right before config update, so Indexer helper is able
     * to check if index needs invalidating.
     *
     * @param \Magento\Config\Model\Config $config
     */
    public function beforeSave(\Magento\Config\Model\Config $config)
    {
        $indexer = $this->_indexer;
        if ($config->getSection() == $indexer::CONFIG_SECTION_ID) {
            $this->_indexer->storeOldConfig();
        }
    }
}
