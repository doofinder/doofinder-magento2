<?php

namespace Doofinder\Feed\Helper;

/**
 * Banner helper
 */
class Banner extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $searchHelper;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Search\SearchClientFactory
     */
    private $searchFactory;

    /**
     * Banner constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Doofinder\Feed\Helper\Search $searchHelper
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Search\SearchClientFactory $searchFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Doofinder\Feed\Helper\Search $searchHelper,
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Search\SearchClientFactory $searchFactory
    ) {
        $this->searchHelper = $searchHelper;
        $this->storeConfig = $storeConfig;
        $this->searchFactory = $searchFactory;
        parent::__construct($context);
    }

    /**
     * Get banner data and register display event.
     *
     * @return array|null
     */
    public function getBanner()
    {
        $isEnabled = (int) $this->storeConfig->isBannersDisplayEnabled($this->getStoreCode());
        if (!$isEnabled) {
            return null;
        }

        $bannerData = $this->searchHelper->getDoofinderBannerData();

        if ($bannerData) {
            $bannerData['insertion_point'] = $this->getInsertionPoint();
            $bannerData['insertion_method'] = $this->getInsertionMethod();
            return $bannerData;
        }

        return null;
    }

    /**
     * Regsiter banner click event for statistics.
     *
     * @param integer $bannerId
     * @return void
     */
    public function registerBannerClick($bannerId)
    {
        $hashId = $this->storeConfig->getHashId($this->getStoreCode());
        $apiKey = $this->storeConfig->getApiKey();
        $client = $this->searchFactory->create($hashId, $apiKey);

        $client->registerBannerClick($bannerId);
    }

    /**
     * Get banner placement.
     *
     * @return string
     */
    private function getInsertionPoint()
    {
        return $this->storeConfig->getBannerInsertionPoint($this->getStoreCode());
    }

    /**
     * Get banner insertion method.
     *
     * @return string
     */
    private function getInsertionMethod()
    {
        return $this->storeConfig->getBannerInsertionMethod($this->getStoreCode());
    }

    /**
     * Returns current store code
     *
     * @return string
     */
    private function getStoreCode()
    {
        return $this->storeConfig->getStoreCode();
    }
}
