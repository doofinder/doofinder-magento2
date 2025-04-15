<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\InstallationOptionsStruct;
use Doofinder\Feed\Model\Data\InstallationStruct;
use Doofinder\Feed\Model\Data\SearchEngineStruct;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Store\Model\Group;

class InstallationRepository
{
    /**
     * @var StoreConfig
     */
    private StoreConfig $storeConfig;

    /**
     * @var SearchEngineRepository
     */
    private SearchEngineRepository $searchEngineRepository;

    /**
     * @var ReadFactory
     */
    private ReadFactory $readFactory;

    public function __construct(
        StoreConfig $storeConfig,
        SearchEngineRepository $searchEngineRepository,
        ReadFactory $readFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchEngineRepository = $searchEngineRepository;
        $this->readFactory = $readFactory;
    }

    public function getByStoreGroup(Group $storeGroup, InstallationOptionsStruct $installationOptions): InstallationStruct
    {
        if ($storeGroup->getDefaultStore() === null) {
            throw new \InvalidArgumentException('Store group does not have a default store.');
        }

        $sector = $this->storeConfig->getValueFromConfig(StoreConfig::SECTOR_VALUE_CONFIG);
        $primaryLanguage = $this->storeConfig->getLanguageFromStore($storeGroup->getDefaultStore());
        $searchEngines = $this->searchEngineRepository->getByStoreGroup($storeGroup);

        $siteUrl = $this->getPrimaryLanguageSiteUrl($primaryLanguage, $searchEngines);

        $pluginVersion = $this->getModuleVersion();

        return new InstallationStruct(
            $storeGroup->getName(),
            "magento2",
            $primaryLanguage,
            false,
            $sector,
            $siteUrl,
            $searchEngines,
            $installationOptions,
            "#search",
            $pluginVersion
        );
    }

    /**
     * We obtain the url associated with the main language search_engine
     */
    private function getPrimaryLanguageSiteUrl(string $primaryLanguage, array $searchEngines): string
    {
        foreach ($searchEngines as $searchEngine) {
            /** @var SearchEngineStruct $searchEngine */
            if ($searchEngine->getLanguage() === $primaryLanguage) {
                return $searchEngine->getSiteurl();
            }
        }

        throw new \RuntimeException('No primary search engine found for the given language.');
    }

    private function getModuleVersion(): string
    {
        $objectManager = ObjectManager::getInstance();
        $moduleInfo =  $objectManager->get('Magento\Framework\Module\ModuleList')->getOne('Doofinder_Feed');

        return $moduleInfo['setup_version'];
    }
}
