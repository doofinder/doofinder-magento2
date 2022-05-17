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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class SearchEngine extends AbstractHelper
{
    public const DOOFINDER_INDEX_PROCESS_STATUS_RECEIVED = 'RECEIVED';

    public const DOOFINDER_INDEX_PROCESS_STATUS_STARTED = 'STARTED';

    public const DOOFINDER_INDEX_PROCESS_STATUS_SUCCESS = 'SUCCESS';

    public const DOOFINDER_INDEX_PROCESS_STATUS_FAILURE = 'FAILURE';

    public const DOOFINDER_GRID_SEVERITY_MINOR = 'minor';

    public const DOOFINDER_GRID_SEVERITY_NOTICE = 'notice';

    public const DOOFINDER_GRID_SEVERITY_MAJOR = 'major';

    public const DOOFINDER_GRID_SEVERITY_CRITICAL = 'critical';

    /** @var ManagementClientFactory  */
    private $managementClientFactory;

    /** @var ThrottleFactory */
    private $throttleFactory;

    /** @var string|null  */
    private $apiKey;

    /** @var array */
    private $searchEngines = [];

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
     * @throws \Zend_Json_Exception
     */
    public function __construct(
        ManagementClientFactory $managementClientFactory,
        ThrottleFactory $throttleFactory,
        StoreManagerInterface $storeManager,
        StoreConfig $storeConfig,
        Context $context,
        string $apiKey = null
    ) {
        $this->managementClientFactory = $managementClientFactory;
        $this->throttleFactory  = $throttleFactory;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->apiKey = $apiKey;
        $this->getSearchEngines();
        parent::__construct($context);
    }

    /**
     * Request all user's search engines throttled
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
     * @throws \Zend_Json_Exception
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
     * @throws \Zend_Json_Exception
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
     * @throws \Zend_Json_Exception
     */
    public function processSearchEngine(string $hashId, string $callbackUrl = null): array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);

        return $managementClient->processSearchEngine($hashId, $callbackUrl);
    }

    /**
     * Gets the status of the last process task throttled
     * @see https://docs.doofinder.com/api/management/v2/#operation/process_status
     *
     * @param string $hashId
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
     */
    public function getProcessTaskStatus(string $hashId): array
    {
        /** @var ManagementClient $managementClient */
        $managementClient = $this->throttleFactory->create([
            'obj' => $this->managementClientFactory->create(['apiKey' => $this->apiKey]),
        ]);

        return $managementClient->getProcessTaskStatus($hashId);
    }

    /**
     * Get search engines array with Hash ID key
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
     * @throws \Zend_Json_Exception
     */
    public function getSearchEngines(): array
    {
        if (!count($this->searchEngines)) {
            foreach ($this->listSearchEngines() as $searchEngine) {
                if ($searchEngine['hashid']) {
                    $this->searchEngines[$searchEngine['hashid']] = $searchEngine;
                }
            }
        }

        return $this->searchEngines;
    }

    /**
     * Get search engine by store code
     *
     * @param string|null $storeCode
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function getSearchEngine(string $storeCode = null): ?array
    {
        $store = $this->storeManager->getStore($storeCode);
        $hashId = $this->storeConfig->getHashId((int)$store->getId());

        return $hashId && isset($this->searchEngines[$hashId]) ? $this->searchEngines[$hashId] : null;
    }

    /**
     * Sanitize and prevent undefined index errors
     *
     * @param array $processTaskStatus
     * @return array
     */
    public function sanitizeProcessTaskStatus(array $processTaskStatus): array
    {
        return [
            'status'        => $processTaskStatus['status'] ?? 'Unknown',
            'result'        => $processTaskStatus['result'] ?? '',
            'finished_at'   => $processTaskStatus['finished_at'] ?? '',
            'error'         => isset($processTaskStatus['error']),
            'error_message' => isset($processTaskStatus['error_message'])
                ? implode(', ', $processTaskStatus['error_message']) : '',
        ];
    }

    /**
     * Get admin grid severity class by status
     *
     * @param array $status
     * @return string
     */
    public function getSeverity(array $status): string
    {
        switch ($status['status']) {
            case self::DOOFINDER_INDEX_PROCESS_STATUS_RECEIVED:
            case self::DOOFINDER_INDEX_PROCESS_STATUS_STARTED:
                $severity = self::DOOFINDER_GRID_SEVERITY_MINOR;
                break;

            case self::DOOFINDER_INDEX_PROCESS_STATUS_SUCCESS:
                $severity = self::DOOFINDER_GRID_SEVERITY_NOTICE;
                break;

            case self::DOOFINDER_INDEX_PROCESS_STATUS_FAILURE:
                $severity = self::DOOFINDER_GRID_SEVERITY_MAJOR;
                break;

            default:
                $severity = self::DOOFINDER_GRID_SEVERITY_CRITICAL;
                break;
        }

        return $severity;
    }
}
