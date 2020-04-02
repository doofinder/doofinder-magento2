<?php

namespace Doofinder\Feed\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Search engine resource model
 */
class Engine implements EngineInterface
{
    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    private $visibility;

    /**
     * Construct
     *
     * @param Visibility $visibility
     */
    public function __construct(Visibility $visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return integer[]
     */
    public function getAllowedVisibility()
    {
        return $this->visibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return boolean
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * @param mixed $attribute
     * @param mixed $value
     * @return mixed
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param mixed $index
     * @param string $separator
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function isAvailable()
    {
        return true;
    }
}
