<?php

declare(strict_types=1);

namespace Doofinder\Feed\Block\Adminhtml\SearchEngines;

use Doofinder\Feed\Helper\SearchEngine;
use Doofinder\Feed\Helper\SearchEngineFactory;
use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Framework\View\Element\Template;

class ProcessStatus extends Template
{
    /** @var SearchEngineFactory  */
    private $searchEngineHelperFactory;

    /** @var SearchEngine  */
    private $searchEngineHelper;

    /** @var StoreConfig */
    private $storeConfig;

    public function __construct(
        SearchEngineFactory $searchEngineHelperFactory,
        StoreConfig $storeConfig,
        Template\Context $context,
        array $data = []
    ) {
        $this->searchEngineHelperFactory = $searchEngineHelperFactory;
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get Search Engines Process Task Status
     *
     * @return array
     */
    public function getSearchEnginesProcessStatus(): array
    {
        $statuses = [];
        try {
            foreach ($this->storeConfig->getAllStores() as $store) {
                $hashId = $this->storeConfig->getHashId((int)$store->getId());
                if ($hashId) {
                    $status = $this->getSearchEngineHelper()->sanitizeProcessTaskStatus(
                        $this->getSearchEngineHelper()->getProcessTaskStatus($hashId)
                    );
                    $statuses[$store->getCode()] = $status;
                    $statuses[$store->getCode()]['name'] = $store->getName();
                    $statuses[$store->getCode()]['severity'] = $this->getSearchEngineHelper()->getSeverity($status);
                }
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'not_found') !== false) {
                $statuses['warning'] =  'not_found';
            } else {
                $statuses['error'] = $e->getMessage();
            }
        }

        return $statuses;
    }

    /**
     * @return SearchEngine
     */
    private function getSearchEngineHelper(): SearchEngine
    {
        if (!$this->searchEngineHelper) {
            $this->searchEngineHelper = $this->searchEngineHelperFactory->create();
        }

        return $this->searchEngineHelper;
    }
}
