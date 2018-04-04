<?php

namespace Doofinder\Feed\Model;

/**
 * Store Website Relation Model
 */
class StoreWebsiteRelation
{
    /**
     * @var ResourceModel\StoreWebsiteRelation
     */
    private $storeWebsiteRelation;

    /**
     * StoreWebsiteRelation constructor.
     * @param ResourceModel\StoreWebsiteRelation $storeWebsiteRelation
     */
    public function __construct(ResourceModel\StoreWebsiteRelation $storeWebsiteRelation)
    {
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * Get store by website id
     * @param integer $websiteId
     * @return array
     */
    public function getStoreByWebsiteId($websiteId)
    {
        return $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
    }
}
