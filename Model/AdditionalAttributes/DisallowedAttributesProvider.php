<?php

namespace Doofinder\Feed\Model\AdditionalAttributes;

use RuntimeException;

/**
 * Class DisallowedAttributesProvider
 * The class responsible for providing attributes that cannot be indexed in Additional Attributes.
 * If attribute is used by indexer by default (for example price or attribute used in search)
 * that means the attribute cannot be configured as an Additional Attribute in Doofinder.
 */
class DisallowedAttributesProvider
{
    /**
     * @var AttributesProviderInterface[]
     */
    private $providers;

    /**
     * @var array
     */
    private $disallowedAttributes;

    /**
     * DisallowedAttributesProvider constructor.
     * @param AttributesProviderInterface[] $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * Collect and return disallowed attribute codes
     * @return array
     * @throws RuntimeException If provider does not implement AttributesProviderInterface.
     */
    public function get()
    {
        if (!$this->disallowedAttributes) {
            $this->disallowedAttributes = [];
            foreach ($this->providers as $provider) {
                if (!$provider instanceof AttributesProviderInterface) {
                    throw new RuntimeException(
                        sprintf(
                            '%s does not implement %s',
                            get_class($provider),
                            AttributesProviderInterface::class
                        )
                    );
                }
                // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $this->disallowedAttributes = array_merge($this->disallowedAttributes, $provider->getAttributes());
                // phpcs:enable
            }
        }

        return $this->disallowedAttributes;
    }
}
