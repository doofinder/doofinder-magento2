<?php

namespace Doofinder\Feed\Model;

use Doofinder\Feed\Helper\StoreConfig;
use Doofinder\Feed\Model\Data\InstallationOptionsStruct;
use Doofinder\Feed\Model\Data\InstallationStruct;
use Doofinder\Feed\Model\Data\SearchEngineStruct;
use InvalidArgumentException;
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

    /**
     * InstallationRepository constructor.
     *
     * @param StoreConfig $storeConfig
     * @param SearchEngineRepository $searchEngineRepository
     * @param ReadFactory $readFactory
     */
    public function __construct(
        StoreConfig $storeConfig,
        SearchEngineRepository $searchEngineRepository,
        ReadFactory $readFactory
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchEngineRepository = $searchEngineRepository;
        $this->readFactory = $readFactory;
    }

    /**
     * Retrieves the installation details for a given store group.
     *
     * @param Group $storeGroup The store group to retrieve installation details for.
     * @param InstallationOptionsStruct $installationOptions Options for the installation process.
     * @return InstallationStruct The installation details for the specified store group.
     * @throws InvalidArgumentException If the store group does not have a valid default store.
     */
    public function getByStoreGroup(
        Group $storeGroup,
        InstallationOptionsStruct $installationOptions
    ): InstallationStruct {
        if ($storeGroup->getDefaultStore() === null) {
            throw new InvalidArgumentException('Store group does not have a default store.');
        }

        $sector = $this->storeConfig->getValueFromConfig(StoreConfig::SECTOR_VALUE_CONFIG);
        $primaryLanguage = $this->storeConfig->getLanguageFromStore($storeGroup->getDefaultStore());
        $searchEngines = $this->searchEngineRepository->getByStoreGroup($storeGroup);

        $siteUrl = $storeGroup->getDefaultStore()->getBaseUrl();

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
     * Retrieves the version of the Doofinder module.
     *
     * @return string The version of the Doofinder module.
     */
    private function getModuleVersion(): string
    {
        $objectManager = ObjectManager::getInstance();
        $moduleInfo =  $objectManager->get(\Magento\Framework\Module\ModuleList::class)->getOne('Doofinder_Feed');

        return $moduleInfo['setup_version'];
    }
}
