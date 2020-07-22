<?php

namespace Doofinder\Feed\Helper;

use Doofinder\Feed\Model\Api\Search;

/**
 * Banner helper
 */
class Banner
{
    /**
     * @var Search
     */
    private $search;

    /**
     * @var StoreConfig
     */
    private $storeConfig;

    /**
     * Banner constructor.
     * @param Search $search
     * @param StoreConfig $storeConfig
     */
    public function __construct(
        Search $search,
        StoreConfig $storeConfig
    ) {
        $this->search = $search;
        $this->storeConfig = $storeConfig;
    }

    /**
     * Get banner data and register display event.
     *
     * @return array|null
     */
    public function getBanner()
    {
        $isEnabled = (bool) $this->storeConfig->isBannersDisplayEnabled($this->getStoreCode());
        if (!$isEnabled) {
            return null;
        }

        $bannerData = $this->search->getBannerData();
        if ($bannerData) {
            $bannerData['insertion_point'] = $this->getInsertionPoint();
            $bannerData['insertion_method'] = $this->getInsertionMethod();
            return $bannerData;
        }

        return null;
    }

    /**
     * Register banner click event for statistics.
     *
     * @param integer $bannerId
     * @return void
     */
    public function registerBannerClick($bannerId)
    {
        $this->search->registerBannerDisplay($bannerId);
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
