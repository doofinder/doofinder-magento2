<?php

namespace Doofinder\Feed\Plugin;

/**
 * Config plugin
 */
class Config
{
    /**
     * @var \Doofinder\Feed\Helper\Indexer
     */
    private $indexer;

    /**
     * Constructor
     *
     * @param \Doofinder\Feed\Helper\Indexer $indexer
     */
    public function __construct(
        \Doofinder\Feed\Helper\Indexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    /**
     * Store doofinder section config
     *
     * This plugins allows to store doofinder section config
     * right before config update, so Indexer helper is able
     * to check if index needs invalidating.
     *
     * @param  \Magento\Config\Model\Config $config
     * @param  mixed $value
     * @return mixed
     */
    public function beforeSave(\Magento\Config\Model\Config $config, $value = null)
    {
        $indexer = $this->indexer;
        if ($config->getSection() == $indexer::CONFIG_SECTION_ID) {
            $this->indexer->storeOldConfig();
        }

        return $value;
    }
}
