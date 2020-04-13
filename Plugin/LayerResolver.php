<?php

namespace Doofinder\Feed\Plugin;

use Magento\Catalog\Model\Layer\Resolver as Subject;
use Doofinder\Feed\Registry\LayerType;

/**
 * Class LayerResolver
 * The class responsible for saving current layer type to use it later in ItemCollection.
 * This is for backward compatibility from Magento 2.3.x where ElasticSearch has a lot abstract logic
 * for all engine search
 * @see https://github.com/magento/magento2/issues/23615
 */
class LayerResolver
{
    /**
     * @var LayerType
     */
    private $layerType;

    /**
     * LayerResolver constructor.
     * @param LayerType $layerType
     */
    public function __construct(LayerType $layerType)
    {
        $this->layerType = $layerType;
    }

    /**
     * Save Layer Type in registry
     * @param Subject $subject
     * @param string $layerType
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
     */
    public function beforeCreate(
        Subject $subject,
        $layerType
    ) {
        // phpcs:enable
        $this->layerType->setLayerType($layerType);
        return [$layerType];
    }
}
