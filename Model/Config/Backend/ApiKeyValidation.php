<?php

namespace Doofinder\Feed\Model\Config\Backend;

/**
 * API key validation backend
 */
class ApiKeyValidation extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Model\Api\SearchEngine
     */
    private $searchEngine;

    /**
     * ApiKeyValidation constructor.
     *
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param \Doofinder\Feed\Model\Api\SearchEngine $searchEngine
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        \Doofinder\Feed\Model\Api\SearchEngine $searchEngine,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->searchEngine = $searchEngine;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

  
    /**
     * Save configuration.
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException Invalid api key.
     */
    public function save()
    {
        $apiKey = $this->getValue();
       
        if ($apiKey) 
        {
            try 
            {
                $this->searchEngine->getSearchEngines($apiKey);
            } 
            catch (\Exception $exception) 
            {
                throw new \Magento\Framework\Exception\ValidatorException(
                __("Something went wrong  : ".$exception->getMessage())
                );    
                
            }
        } elseif ($this->storeConfig->isInternalSearchEnabled()) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('API key cannot be empty when Doofinder engine is enabled.')
            );
        }
        else
        {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('API key is invalid.')
            );

        }

        return parent::save();
    }
}
