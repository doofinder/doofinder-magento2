<?php

namespace Doofinder\Feed\Controller\Feed;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Module\ModuleListInterface;
use Doofinder\Feed\Helper\StoreConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Config controller
 */
class Config extends Action
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * Config constructor.
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleListInterface $moduleList
     * @param StoreConfig $storeConfig
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        StoreConfig $storeConfig
    ) {
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->storeConfig = $storeConfig;
        parent::__construct($context);
    }

    /**
     * Returns config json
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $config = [
            'platform' => [
                'name' => 'Magento',
                'edition' => $this->productMetadata->getEdition(),
                'version' => $this->productMetadata->getVersion(),
            ],
            'module' => [
                'version' => $this->moduleList->getOne(StoreConfig::MODULE_NAME)['setup_version'],
                'options' => [
                    'language' => [],
                ],
                'configuration' => [],
            ],
        ];

        foreach ($this->storeConfig->getAllStores() as $store) {
            $storeCode = $store->getCode();

            $config['module']['options']['language'][] = $storeCode;
            $config['module']['configuration'][$storeCode] = [
                'language' => $this->getLanguage($storeCode),
                'currency' => $store->getCurrentCurrencyCode(),
            ];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($config);
    }

    /**
     * @param string $storeCode
     * @return string
     */
    private function getLanguage($storeCode)
    {
        return strtoupper(
            substr($this->storeConfig->getStoreLanguage($storeCode), 0, 2)
        );
    }
}
