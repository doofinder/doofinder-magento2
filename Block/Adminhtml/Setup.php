<?php

declare(strict_types=1);

namespace Doofinder\Feed\Block\Adminhtml;

use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Block\Adminhtml\Integration\Tokens;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\ResourceModel\Integration\Collection as IntegrationCollection;
use Magento\Integration\Model\ResourceModel\Integration\CollectionFactory as IntegrationCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Setup extends Template
{
    /**
     * @var IntegrationCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * Setup constructor.
     *
     * @param Template\Context $context
     * @param IntegrationCollectionFactory $collectionFactory
     * @param StoreConfig $storeConfig
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     * @param IntegrationServiceInterface $integrationService
     */
    public function __construct(
        Template\Context $context,
        IntegrationCollectionFactory $collectionFactory,
        StoreConfig $storeConfig,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        IntegrationServiceInterface $integrationService
    ) {
        $this->storeConfig = $storeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
        $this->integrationService = $integrationService;
        parent::__construct($context, []);
    }

    /**
     * Get the params for the popup link account window
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getParamsPopup(): string
    {
        $redirect = $this->storeManager->getStore()->getBaseUrl() .
            'doofinderfeed/setup/config';

        $token = $this->encryptor->hash($redirect);

        return 'email=' . $this->storeConfig->getEmailAdmin()
            . '&token=' . $token
            . '&shop_url=' . urlencode($this->storeManager->getStore()->getBaseUrl())
            . '&return_path=' . urlencode($redirect);
    }

    /**
     * Is integration created
     *
     * @return bool
     */
    public function isIntegrationCreated(): bool
    {
        $collection = $this->getIntegrationCollection();

        return $collection->getSize() > 0;
    }

    /**
     * Get if we are linked or not to an account
     *
     * @return bool
     */
    public function hasApiKey(): bool
    {
        return ($this->storeConfig->getApiKey() !== null && !empty($this->storeConfig->getApiKey()));
    }

    /**
     * Get Save url
     *
     * @return string
     */
    public function getSaveUrl(): string
    {
        return $this->getUrl('*/integration/save');
    }

    /**
     * Get Test API KEY url
     *
     * @return string
     */
    public function getCheckAPIKeyUrl(): string
    {
        return $this->getUrl('*/*/check');
    }

    /**
     * Get create search engines url
     *
     * @return string
     */
    public function getCreateSearchEnginesUrl(): string
    {
        return $this->getUrl('*/integration/createSearchEngines');
    }

    /**
     * Get create indices url
     *
     * @return string
     */
    public function getCreateIndicesUrl(): string
    {
        return $this->getUrl('*/integration/createIndices');
    }

    /**
     * Get process search engines url
     *
     * @return string
     */
    public function getProcessSearchEnginesUrl(): string
    {
        return $this->getUrl('*/integration/processSearchEngines');
    }

    /**
     * Get create display layers url
     *
     * @return string
     */
    public function getCreateDisplayLayersUrl(): string
    {
        return $this->getUrl('*/integration/createDisplayLayers');
    }

    /**
     * Get Permissions dialog url
     *
     * @return string
     */
    public function getPermissionsDialogUrl(): string
    {
        return $this->getUrl(
            'adminhtml/integration/permissionsDialog',
            ['id' => ':id', 'reauthorize' => '0', '_escape_params' => false]
        );
    }

    /**
     * Get Permissions tokens dialog url
     *
     * @return string
     */
    public function getTokensDialogUrl(): string
    {
        return $this->getUrl(
            'adminhtml/integration/tokensDialog',
            ['id' => ':id', 'reauthorize' => '0', '_escape_params' => false]
        );
    }

    /**
     * Get integration access token URL
     *
     * @return string
     */
    public function getAccessTokenUrl(): string
    {
        return $this->getUrl(
            '*/*/accessToken',
            ['id' => ':id', '_escape_params' => false]
        );
    }

    /**
     * Get Save Integration ID in system config url
     *
     * @return string
     */
    public function getSaveConfigUrl(): string
    {
        return $this->getUrl('*/integration/saveConfig');
    }

    /**
     * Get index processing status URL
     *
     * @return string
     */
    public function getIndexProcessingStatusUrl(): string
    {
        return $this->getUrl('doofinderfeed/searchEngines/processStatus');
    }

    /**
     * Get Doofinder configuration URL
     *
     * @return string
     */
    public function getDoofinderConfigurationUrl(): string
    {
        return $this->getUrl('adminhtml/system_config/edit/section/doofinder_config_config');
    }

    /**
     * Get initial setup installing loop status
     *
     * @return int
     */
    public function getInstallingLoopStatus(): int
    {
        return $this->storeConfig->getInstallingLoopStatus();
    }

    /**
     * Get access token
     *
     * @return string|null
     */
    private function getIntegrationToken(): ?string
    {
        $collection = $this->getIntegrationCollection();
        if ($collection->getSize()) {
            $integrationId = $collection->getFirstItem()->getId();
            try {
                $integration = $this->integrationService->get($integrationId);
                return $integration->getData(Tokens::DATA_TOKEN);
            } catch (\Exception $e) {
                //silence is golden
            }
        }

        return null;
    }

    /**
     * Get integration collection
     *
     * @return IntegrationCollection
     */
    private function getIntegrationCollection(): IntegrationCollection
    {
        return $this->collectionFactory->create()->addFieldToFilter(
            Integration::NAME,
            $this->storeConfig->getIntegrationName()
        );
    }
}
