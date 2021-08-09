<?php

namespace Doofinder\Feed\Test\Helper;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Doofinder\Feed\Helper\StoreConfig;

/**
 * Helper class
 */
class Configuration
{
    const TEST_API_KEY = '384fdag73c7ff0a59g589xf9f4083bxb9727f9c3';
    const TEST_HASH_ID = 'cc79e589e94b0350fb244e477e0f5b7a';
    
    const DOOFINDER_API_KEY_PATH = 'doofinder_config_config/doofinder_account/api_key';
    const DOOFINDER_HASH_ID_PATH = 'doofinder_config_config/doofinder_search_engine/hash_id';

    const DOOFINDER_SEARCH_SERVER_PATH = 'doofinder_config_config/doofinder_account/search_server';
    const DOOFINDER_SEARCH_SERVER_VALUE = 'https://eu1-search.doofinder.com';
    const DOOFINDER_MANAGEMENT_SERVER_PATH = 'doofinder_config_config/doofinder_account/management_server';
    const DOOFINDER_MANAGEMENT_SERVER_VALUE = 'https://eu1-api.doofinder.com';
    

    const SEARCH_ENGINE_PATH = 'catalog/search/engine';
    const SEARCH_ENGINE_DOOFINDER = 'doofinder';
    const SEARCH_ENGINE_MYSQL = 'mysql';

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var MutableScopeConfigInterface */
    private $mutableScopeConfig;

    /** @var IndexerRegistry */
    private $indexerRegistry;

    public function __construct() {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->mutableScopeConfig = $this->objectManager->create(MutableScopeConfigInterface::class);
        $this->indexerRegistry = $this->objectManager->get(IndexerRegistry::class);
    }

    /**
     * Scenario 1:
     *  - Search Engine: MySQL (anything but doofinder)
     *  - Doofinder indice update mode: api
     *  - Magento catalosearch update mode: schedule
     */
    public function setupConfigScenario1() {
        $this->setupConfig([
            'default' => [
                self::SEARCH_ENGINE_PATH => self::SEARCH_ENGINE_MYSQL,
            ],
            'store' => [
                StoreConfig::INDICES_UPDATE_MODE => StoreConfig::DOOFINDER_INDICES_UPDATE_API,
            ]
        ]);
        $this->changeIndexerSchedule(FulltextIndexer::INDEXER_ID, true);
    }

    public function setupDoofinder() {
        $this->setupConfig([
            'default' => [
                self::DOOFINDER_API_KEY_PATH => self::TEST_API_KEY,
                self::DOOFINDER_SEARCH_SERVER_PATH => self::DOOFINDER_SEARCH_SERVER_VALUE,
                self::DOOFINDER_MANAGEMENT_SERVER_PATH => self::DOOFINDER_MANAGEMENT_SERVER_VALUE,
            ],
            'store' => [
                self::DOOFINDER_HASH_ID_PATH => self::TEST_HASH_ID,
                StoreConfig::INDICES_UPDATE_MODE => null
            ] 
        ]);
    }

    public function setupConfig(array $config) {
        foreach ($config as $scope => $conf) {
            foreach ($conf as $path => $value) {
                $this->mutableScopeConfig->setValue(
                    $path, 
                    $value, 
                    $scope
                );
            }
        }
    }

    public function cleanConfig() {
        $this->mutableScopeConfig->clean();
    }

    /**
     * Changes the scheduled state of indexer.
     *
     * @param string $indexerId
     * @param bool $isScheduled
     * @return void
     */
    public function changeIndexerSchedule(string $indexerId, bool $isScheduled): void
    {
        $indexer = $this->getIndexer($indexerId);
        $indexer->setScheduled($isScheduled);
    }

    /**
     * Gets indexer from registry by ID.
     *
     * @param string $indexerId
     * @return IndexerInterface
     */
    public function getIndexer(string $indexerId)
    {
        return $this->indexerRegistry->get($indexerId);
    }
}