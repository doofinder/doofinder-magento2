<?php

declare(strict_types=1);

namespace Doofinder\Feed\Block\Adminhtml\SearchEngines;

use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Framework\View\Element\Template;

class ProcessStatus extends Template
{
    /** @var StoreConfig */
    private $storeConfig;

    public function __construct(
        StoreConfig $storeConfig,
        Template\Context $context,
        array $data = []
    ) {
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
            $stores = $this->storeConfig->getAllStores();
            foreach ($stores as $store) {
                $status = $this->storeConfig->getIndexationStatus((int)$store->getId());
                $statuses[$store->getCode()] = $status;
                $statuses[$store->getCode()]['name'] = $store->getName();
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
}
