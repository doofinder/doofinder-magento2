<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\Processor;

class AtomicUpdater extends Component implements Processor
{
    /**
     * @var \DoofinderManagementApi
     */
    protected $_doofinderManagementApi;

    /**
     * Constructor
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \DoofinderManagementApiFactory $dmaFactory,
        array $data = []
    ) {
        parent::__construct($logger, $data);

        // Create DoofinderManagementApi instance
        $this->_doofinderManagementApi = $dmaFactory->create($this->getData('api_key'));

        // Prepare SearchEngine instance
        $hashId = $this->getData('hash_id');
        foreach ($this->_doofinderManagementApi->getSearchEngines() as $searchEngine) {
            if ($searchEngine->hashid == $hashId) {
                $this->_searchEngine = $searchEngine;
                break;
            }
        }

        if (!$this->_searchEngine) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Search engine with HashID %1 doesn\'t exists. Please, check your configuration.', $hashId)
            );
        }
    }

    /**
     * Process items
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
     */
    public function process(array $items)
    {
        $this->_searchEngine->updateItems('product', array_map(function ($item) {
            return $item->getData();
        }, $items));
    }
}
