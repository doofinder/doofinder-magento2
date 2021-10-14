<?php

namespace Doofinder\Feed\Model\Config\Source\Feed;

use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
/**
 * Class SearchableAttributes
 * The class responsible for providing options in system configuration
 */
class SearchableAttributes implements ArrayInterface
{  
    /**
    * @var CollectionFactory
    */
   private $collectionFactory;

   /**
    * @var Config
    */
   private $eavConfig;

   /**
    * @var array
    */
   private $magentoAttributes;

    /**
     * Attributes constructor.
     */
    public function __construct( CollectionFactory $collectionFactory,
    Config $eavConfig)
    {
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
    }
    
    public function getAdvancedSearchAttribute()
    {
        $excludedTypes = ['media_image','image','date','gallery','boolean'];
        
        if (!$this->magentoAttributes) 
        {
            $eavType = $this->eavConfig->getEntityType(Product::ENTITY);
            $attributes = $this->collectionFactory->create()->setEntityTypeFilter($eavType);
            foreach ($attributes as $attribute) 
            {
                if($attribute->getIsSearchable())
                {
                    //check for excluded types
                    if (!in_array($attribute->getFrontendInput(), $excludedTypes, true)) 
                    {
                        $this->magentoAttributes[] = $attribute->getAttributeCode();
                    }                    
                }
            }

            $this->magentoAttributes = array_unique($this->magentoAttributes);
        }

        return $this->magentoAttributes;
    }
    /**
     * Return array of options as value-label pairs, eg. attribute_code => attribute_label.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $searchableAttributes = $this->getAdvancedSearchAttribute();
        foreach ($searchableAttributes as $attribute) 
        {        
            $options[] = 
            [
                'label' => $attribute,
                'value' => $attribute
            ];
        }
       
            //add blank item
            array_unshift( $options,[
                'label' =>'None',
                'value' =>'None'
            ]);
    
    
        return $options;
    }
}
