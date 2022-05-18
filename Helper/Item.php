<?php
declare(strict_types=1);


namespace Doofinder\Feed\Helper;

use Doofinder\Feed\ApiClient\ManagementClient;
use Doofinder\Feed\ApiClient\ManagementClientFactory;
use Doofinder\Feed\Errors\BadRequest;
use Doofinder\Feed\Errors\IndexingInProgress;
use Doofinder\Feed\Errors\NotAllowed;
use Doofinder\Feed\Errors\NotFound;
use Doofinder\Feed\Errors\QuotaExhausted;
use Doofinder\Feed\Errors\ThrottledResponse;
use Doofinder\Feed\Errors\TypeAlreadyExists;
use Doofinder\Feed\Errors\WrongResponse;
use Doofinder\Feed\Wrapper\Throttle;
use Doofinder\Feed\Wrapper\ThrottleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

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
     * Create or update item
     *
     * @param array $item
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NoSuchEntityException
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     */
    public function saveItem(array $item, StoreInterface $store, string $indice): array
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        try {
            $this->getItem($item['id'], $hashId, $indice);
            $item = $this->updateItem($item, $item['id'], $hashId, $indice);
        } catch (NotFound $e) {
            $item = $this->createItem($item, $hashId, $indice);
        }

        return $item;
    }

    /**
     * Get item throtled
     *
     * @param int $itemId
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     * @throws NoSuchEntityException
     */
    public function getItem(int $itemId, StoreInterface $store, string $indice): array
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(),
        ]);

        return $managementClient->getItem($itemId, $hashId, $indice);
    }

    /**
     * Create item throttled
     *
     * @param array $item
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     * @throws NoSuchEntityException
     */
    public function createItem(array $item, StoreInterface $store, string $indice): array
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(),
        ]);

        return $managementClient->createItem($item, $hashId, $indice);
    }

    /**
     * Update item throttled
     *
     * @param array $item
     * @param int $itemId
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     * @throws NoSuchEntityException
     */
    public function updateItem(
        array $item,
        int $itemId,
        StoreInterface $store,
        string $indice
    ): array {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(),
        ]);

        return $managementClient->updateItem($item, $itemId, $hashId, $indice);
    }

    /**
     * Create items in bulk throttled
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     * @throws NoSuchEntityException
     */
    public function createItemsInBulk(array $items, StoreInterface $store, string $indice): array
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(),
        ]);

        return $managementClient->createItemsInBulk($items, $hashId, $indice);
    }

    /**
     * Update items in bulk throttled
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     * @throws NoSuchEntityException
     */
    public function updateItemsInBulk(array $items, StoreInterface $store, string $indice): array
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(),
        ]);

        return $managementClient->updateItemsInBulk($items, $hashId, $indice);
    }

    /**
     * Delete items in bulk throttled
     *
     * @param array $items
     * @param StoreInterface $store
     * @param string $indice
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Zend_Json_Exception
     * @throws NoSuchEntityException
     */
    public function deleteItemsInBulk(array $items, StoreInterface $store, string $indice): array
    {
        $searchEngine = $this->getSearchEngineFromStore($store);
        $hashId = $searchEngine['hashid'];
        $this->validateIndice($searchEngine, $indice);
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(),
        ]);

        return $managementClient->deleteItemsInBulk($items, $hashId, $indice);
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
        $searchEngine = $this->searchEngine->getSearchEngine($store->getCode());
        if ($searchEngine === null) {
            throw new NotFound('There is not a valid Hash ID configured for the current store.');
        }

        return $searchEngine;
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
        $this->indice->getIndiceFromSearchEngine($searchEngine, $indice);
    }
}
