<?php
declare(strict_types=1);


namespace Doofinder\Feed\Helper;

use Doofinder\Feed\Api\Data\ChangedItemInterface;
use Doofinder\Feed\ApiClient\Client;
use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Errors\BadRequest;
use Doofinder\Feed\Errors\IndexingInProgress;
use Doofinder\Feed\Errors\NotAllowed;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Errors\QuotaExhausted;
use Doofinder\Feed\Errors\ThrottledResponse;
use Doofinder\Feed\Errors\TypeAlreadyExists;
use Doofinder\Feed\Errors\WrongResponse;
use Doofinder\Feed\Wrapper\ThrottleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;

class Item extends AbstractHelper
{
    /** @var ManagementClientFactory  */
    private $managementClientFactory;

    /** @var ThrottleFactory */
    private $throttleFactory;

    /** @var SearchEngine */
    private $searchEngine;

    /** @var Indice */
    private $indice;

    /**
     * Item constructor.
     *
     * @param ManagementClientFactory $managementClientFactory
     * @param ThrottleFactory $throttleFactory
     * @param SearchEngine $searchEngine
     * @param Indice $indice
     * @param Context $context
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        ThrottleFactory $throttleFactory,
        SearchEngine $searchEngine,
        Indice $indice,
        Context $context
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->throttleFactory  = $throttleFactory;
        $this->searchEngine = $searchEngine;
        $this->indice = $indice;
        parent::__construct($context);
    }

    /**
     * Create items in bulk throttled
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     * @throws NoSuchEntityException
     */
    public function createItemsInBulk(array $items, StoreInterface $store, string $indice)
    {
        return $this->processItemsInBulk($items, $store, $indice, ChangedItemInterface::OPERATION_TYPE_CREATE);
    }

    /**
     * Update items in bulk throttled
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     * @throws NoSuchEntityException
     */
    public function updateItemsInBulk(array $items, StoreInterface $store, string $indice)
    {
        return $this->processItemsInBulk($items, $store, $indice, ChangedItemInterface::OPERATION_TYPE_UPDATE);
    }

    /**
     * Delete items in bulk throttled
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     * @throws NoSuchEntityException
     */
    public function deleteItemsInBulk(array $items, StoreInterface $store, string $indice)
    {
        return $this->processItemsInBulk($items, $store, $indice, ChangedItemInterface::OPERATION_TYPE_DELETE);
    }

    /**
     * Get search engine from store
     *
     * @param StoreInterface $store
     * @return array
     * @throws NoSuchEntityException
     * @throws NotFound
     */
    private function getSearchEngineFromStore(StoreInterface $store): array
    {
        $searchEngine = $this->searchEngine->getSearchEngineByStore($store);
        if ($searchEngine === null) {
            throw new NotFound('There is not a valid Hash ID configured for the current store.');
        }

        return $searchEngine;
    }

    /**
     * Processes items depending on the type os processing expected
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @param string $type
     */
    private function processItemsInBulk(array $items, StoreInterface $store, string $indice, string $type)
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiType' => Client::DOOPLUGINS]),
        ]);

        switch ($type) {
            case ChangedItemInterface::OPERATION_TYPE_CREATE:
                return $managementClient->createItemsInBulk($items, $hashId, $indice);
            case ChangedItemInterface::OPERATION_TYPE_UPDATE:
                return $managementClient->updateItemsInBulk($items, $hashId, $indice);
            case ChangedItemInterface::OPERATION_TYPE_DELETE:
                return $managementClient->deleteItemsInBulk($items, $hashId, $indice);
        }
    }

    /**
     * Checks if indice exists in search engine
     *
     * @param array $searchEngine
     * @param string $indice
     * @throws NotFound
     */
    private function validateIndice(array $searchEngine, string $indice): void
    {
        if (!$this->indice->checkIndiceExistsInSearchEngine($searchEngine, $indice)) {
            throw new NotFound(
                sprintf("Indice '%s' doesn't exist in search engine '%s'", $indice, $searchEngine['name'])
            );
        }
    }
}
