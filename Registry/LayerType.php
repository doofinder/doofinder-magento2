<?php

namespace Doofinder\Feed\Registry;

/**
 * Class LayerType
 * Registry for keeping current layer type
 */
class LayerType
{
    /**
     * @var string|null
     */
    private $layerType;

    /**
     * @param string $type
     * @return void
     */
    public function setLayerType($type)
    {
        $this->layerType = $type;
    }

    /**
     * @return string|null
     */
    public function getLayerType()
    {
        return $this->layerType;
    }
}
