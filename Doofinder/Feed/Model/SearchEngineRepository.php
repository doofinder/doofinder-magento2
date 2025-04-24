<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\SearchEngineOptionsStruct;
use Doofinder\Feed\Model\Data\SearchEngineStruct;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;

class SearchEngineRepository
{
    private const INDEX_URL_FORMAT = 'rest/%s/V1/';

    private const PROCESS_CALLBACK_PATH = 'doofinderfeed/setup/processCallback';
    /**
     * @var StoreConfig
     */
    private StoreConfig $storeConfig;

    public function __construct(
        StoreConfig $storeConfig
    ) {
        $this->storeConfig = $storeConfig;
    }

    /**
     * Retrieves unique search engine configurations for a store group based on language and currency.
     *
     * This method processes all stores within the provided store group to generate a list of
     * search engine configurations. Each configuration is unique to a specific language and
     * currency combination, ensuring no duplicates are created.
     *
     * For each store in the store group:
     * - Retrieves the search engine configuration for the store.
     * - Checks if the language-currency pair is already processed.
     * - If not, adds the configuration to the result and marks the pair as processed.
     *
     * Returns an array of search engine configurations, where each entry corresponds to a
     * unique language-currency combination.
     *
     * @param Group $storeGroup The store group containing the stores to process.
     * @return SearchEngineStruct[] An array of unique search engine configurations.
     */
    public function getByStoreGroup(Group $storeGroup): array
    {
        $searchEngines = [];
        $languagesCurrencies = [];

        $stores = $storeGroup->getStores();
        if (empty($stores)) {
            return $searchEngines; // Return empty array if no stores are found
        }

        /** @var Store $store */
        foreach ($stores as $store) {
            $newSearchEngine = $this->getByStore($store);

            // Only generate config for a unique pair. The rest of Store Views must be created explicitly
            if (isset($languagesCurrencies[$newSearchEngine->getLanguage()][$newSearchEngine->getCurrency()])) {
                continue;
            }

            $searchEngines[] = $newSearchEngine;

            $languagesCurrencies[] =
                $languagesCurrencies[$newSearchEngine->getLanguage()][$newSearchEngine->getCurrency()] = true;
        }

        return $searchEngines;
    }

    public function getByStore(Store $store): SearchEngineStruct
    {
        $language = $this->storeConfig->getLanguageFromStore($store);
        $currency = strtoupper($store->getCurrentCurrency()->getCode());
        $storeId = $store->getId();
        $storeCode = $store->getCode();
        $baseUrl = $store->getBaseUrl();

        $installationId = $this->storeConfig->getInstallationId($store->getStoreGroupId());

        return  new SearchEngineStruct(
            $store->getGroup()->getName() . ' - ' . $store->getName(),
            $language,
            $currency,
            $baseUrl . self::PROCESS_CALLBACK_PATH . '?storeId=' . $storeId,
            new SearchEngineOptionsStruct(
                (string)$storeId,
                $baseUrl . sprintf(self::INDEX_URL_FORMAT, $storeCode),
            ),
            $installationId
        );
    }
}
