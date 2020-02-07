<?php

namespace Doofinder\Feed\Plugin;

use Magento\Catalog\Model\Layer\ContextInterface as Subject;
use Magento\Search\Model\EngineResolver;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Doofinder\Feed\Registry\LayerType;

/**
 * Class ItemCollection
 * The class responsible for providing correct ItemCollection
 * This is for backward compatibility from Magento 2.3.x where ElasticSearch has a lot abstract logic
 * for all engine search
 * @see https://github.com/magento/magento2/issues/23615
 */
class ItemCollection
{
    /**
     * @var EngineResolver
     */
    private $engineResolver;

    /**
     * @var ItemCollectionProviderInterface
     */
    private $catItemCollection;

    /**
     * @var ItemCollectionProviderInterface
     */
    private $searchItemCollection;

    /**
     * @var LayerType
     */
    private $layerType;

    /**
     * ItemCollection constructor.
     * @param EngineResolver $engineResolver
     * @param ItemCollectionProviderInterface $catItemCollection
     * @param ItemCollectionProviderInterface $searchItemCollection
     * @param LayerType $layerType
     */
    public function __construct(
        EngineResolver $engineResolver,
        ItemCollectionProviderInterface $catItemCollection,
        ItemCollectionProviderInterface $searchItemCollection,
        LayerType $layerType
    ) {
        $this->engineResolver = $engineResolver;
        $this->catItemCollection = $catItemCollection;
        $this->searchItemCollection = $searchItemCollection;
        $this->layerType = $layerType;
    }

    /**
     * Check if Doofinder is current engine search and return specific item collection.
     * In other case, proceed with default implementation
     * @param Subject $subject
     * @param callable $proceed
     * @return ItemCollectionProviderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCollectionProvider(
        Subject $subject,
        callable $proceed
    ) {
        if ($this->engineResolver->getCurrentSearchEngine() == 'doofinder') {
            if ($this->layerType->getLayerType() == 'category') {
                return $this->catItemCollection;
            } elseif ($this->layerType->getLayerType() == 'search') {
                return $this->searchItemCollection;
            }
        }
        return $proceed();
    }
}
