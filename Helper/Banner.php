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
        $bannerData = $this->searchHelper->getDoofinderBannerData();
        $isEnabled = (int) $this->storeConfig->isBannersDisplayEnabled($this->getStoreCode());

        if ($bannerData && $isEnabled) {
            $this->registerBannerDisplay($bannerData['id']);
            $bannerData['placement'] = $this->getBannerPlacement();
            return $bannerData;
        }

        return null;
    }

    /**
     * Regsiter banner display event for statistics.
     *
     * @param integer $bannerId
     * @return void
     */
    private function registerBannerDisplay($bannerId)
    {
        $hashId = $this->storeConfig->getHashId($this->getStoreCode());
        $apiKey = $this->storeConfig->getApiKey();
        $client = $this->searchFactory->create($hashId, $apiKey);

        $client->registerBannerDisplay($bannerId);
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
    private function getBannerPlacement()
    {
        return $this->storeConfig->getBannerPlacementAfter($this->getStoreCode());
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
