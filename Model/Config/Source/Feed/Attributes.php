<?php

namespace Doofinder\Feed\Model\Config\Source\Feed;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Attributes
 * The class responsible for providing options in system configuration
 */
class Attributes implements ArrayInterface
{
    /**
     * @var ArrayInterface[]
     */
    private $providers;

    /**
     * @var array
     */
    private $options = [];

    /**
     * Attributes constructor.
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * Return array of options as value-label pairs, eg. attribute_code => attribute_label.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (empty($this->options)) {
            foreach ($this->providers as $provider) {
                if ($provider instanceof ArrayInterface) {
                    // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                    $this->options = array_merge(
                        $this->options,
                        $provider->toOptionArray()
                    );
                    // phpcs:enable
                }
            }
        }
        return $this->options;
    }
}
