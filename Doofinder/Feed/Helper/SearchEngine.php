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
use Doofinder\Feed\Wrapper\ThrottleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class SearchEngine extends AbstractHelper
{

    /** @var ManagementClientFactory  */
    private $managementClientFactory;

    /** @var ThrottleFactory */
    private $throttleFactory;

    /** @var string|null  */
    private $apiKey;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var StoreConfig */
    private $storeConfig;

    /**
     * Search Engine constructor.
     *
     * @param ManagementClientFactory $managementClientFactory
     * @param ThrottleFactory $throttleFactory
     * @param StoreManagerInterface $storeManager
     * @param StoreConfig $storeConfig
     * @param Context $context
     * @param string|null $apiKey
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        ThrottleFactory $throttleFactory,
        StoreManagerInterface $storeManager,
        StoreConfig $storeConfig,
        Context $context,
        ?string $apiKey = null
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->throttleFactory  = $throttleFactory;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->apiKey = $apiKey;
        parent::__construct($context);
    }

    /**
     * Request all user's search engines throttled
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/search_engine_list
     *
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function listSearchEngines(): array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);

        return $managementClient->listSearchEngines();
    }

    /**
     * Create search engine throttled
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/search_engine_list
     *
     * @param array $searchEngine
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function createSearchEngine(array $searchEngine): array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);

        return $managementClient->createSearchEngine($searchEngine);
    }

    /**
     * Process search engine throttled
     *
     * @see https://docs.doofinder.com/api/management/v2/#operation/process
     *
     * @param string $hashId
     * @param string|null $callbackUrl
     * @return array
     * @throws BadRequest
     * @throws IndexingInProgress
     * @throws NotAllowed
     * @throws NotFound
     * @throws QuotaExhausted
     * @throws ThrottledResponse
     * @throws TypeAlreadyExists
     * @throws WrongResponse
     * @throws \Exception
     */
    public function processSearchEngine(string $hashId, ?string $callbackUrl = null): array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);

        return $managementClient->processSearchEngine($hashId, $callbackUrl);
    }

    /**
     * Get search engine by hashid
     *
     * @param string|null $hashId
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function getSearchEngine(string $hashId): ?array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);
        return $managementClient->getSearchEngine($hashId);
    }

    /**
     * Get search engine by store code
     *
     * @param StoreInterface $store
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function getSearchEngineByStore(StoreInterface $store): ?array
    {
        $hashId = $this->storeConfig->getHashId((int)$store->getId());
        return $this->getSearchEngine($hashId);
    }
}
