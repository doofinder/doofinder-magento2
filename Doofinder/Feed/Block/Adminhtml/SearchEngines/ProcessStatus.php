<?php

declare(strict_types=1);

namespace Doofinder\Feed\Block\Adminhtml\SearchEngines;

use Doofinder\Feed\Helper\StoreConfig;
use Exception;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Escaper;

class ProcessStatus extends Template
{
    /** @var StoreConfig */
    private $storeConfig;

    /** @var Escaper */
    private $escaper;

    /**
     * @param StoreConfig $storeConfig
     * @param Template\Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        StoreConfig $storeConfig,
        Template\Context $context,
        Escaper $escaper,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Make Escaper available to the template
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Get Search Engines Process Task Status
     *
     * @return mixed[]
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
