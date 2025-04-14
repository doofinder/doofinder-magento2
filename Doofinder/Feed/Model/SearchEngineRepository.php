<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\SearchEngineOptionsStruct;
use Doofinder\Feed\Model\Data\SearchEngineStruct;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;

class SearchEngineRepository
{

    private const PROCESS_CALLBACK_PATH = 'doofinderfeed/setup/processCallback';
    private const REST_API_PATH = 'rest/%s/V1/';

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
     * Get search engines for a store group unique by language and currency
     *
     * @param Group $storeGroup
     * @return SearchEngineStruct[]
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
            $language = $this->storeConfig->getLanguageFromStore($store);
            $currency = strtoupper($store->getCurrentCurrency()->getCode());

            // Only generate config for a unique pair. The rest of Store Views must be created explicitly
            if (isset($languagesCurrencies[$language][$currency])) {
                continue;
            }

            $storeId = $store->getId();
            $baseUrl = $store->getBaseUrl();

            $searchEngines[] = new SearchEngineStruct(
                $store->getGroup()->getName() . ' - ' . $store->getName(),
                $language,
                $currency,
                $baseUrl,
                $baseUrl . self::PROCESS_CALLBACK_PATH . '?storeId=' . $storeId,
                new SearchEngineOptionsStruct(
                    (string)$storeId,
                    $baseUrl . sprintf(self::REST_API_PATH, $store->getCode())
                )
            );
            $languagesCurrencies[$language][$currency] = true;
        }

        return $searchEngines;
    }
}
