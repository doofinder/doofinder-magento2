<?php

namespace Doofinder\Feed\Observer;

/**
 * Class ProductAtomicUpdate
 *
 * @package Doofinder\Feed\Observer
 */
class ProductAtomicUpdate implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_feedConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Doofinder\Feed\Model\GeneratorFactory
     */
    protected $_generatorFactory;

    /**
     * Class constructur
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Helper\FeedConfig $feedConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Doofinder\Feed\Model\GeneratorFactory $generatorFactory
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_feedConfig = $feedConfig;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_generatorFactory = $generatorFactory;
    }

    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $originalStoreCode = $this->_storeConfig->getStoreCode();

        $storeCodes = $this->_storeConfig->getStoreCodes();

        foreach ($storeCodes as $storeCode) {
            // Skip store if atomic updates not enabled
            if (!$this->_storeConfig->isAtomicUpdatesEnabled($storeCode)) {
                continue;
            }

            $this->_storeManager->setCurrentStore($storeCode);

            $apiKey = $this->_storeConfig->getApiKey();
            $hashId = $this->_storeConfig->getHashId($storeCode);

            if (!$hashId) {
                $message = __('HashID is not set for the \'%1\' store view, therefore,
                    search indexes haven\'t been updated for this store view. To fix this
                    problem set HashID for a given stor view or disable Internal Search in
                    Doofinder Search Configuration.', $storeCode);

                $this->_messageManager->addWarning($message);
                continue;
            }

            $feedConfig = $this->_feedConfig->getLeanFeedConfig($storeCode);

            // Add fixed product fetcher
            $feedConfig['data']['config']['fetchers']['Product\Fixed'] = [
                'products' => $observer->getEvent()->getProduct(),
            ];

            // Add atomic update processor
            $feedConfig['data']['config']['processors']['AtomicUpdater'] = [
                'hash_id' => $hashId,
                'api_key' => $apiKey,
            ];

            $generator = $this->_generatorFactory->create($feedConfig);
            $generator->run();
        }

        // Finally set back original store code
        $this->_storeManager->setCurrentStore($originalStoreCode);
    }
}
