<?php

namespace Doofinder\Feed\Model\AdditionalAttributes\Provider;

use Doofinder\Feed\Model\AdditionalAttributes\AttributesProviderInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

/**
 * Class Magento
 * The class responsible for providing Magento attributes code
 */
class Magento implements AttributesProviderInterface
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var array
     */
    private $magentoAttributes;

    /**
     * Magento constructor.
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritDoc}
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->magentoAttributes) {
            $attributes = $this->dataProvider->getSearchableAttributes();
            foreach ($attributes as $attribute) {
                $this->magentoAttributes[] = $attribute->getAttributeCode();
            }

            $this->magentoAttributes = array_unique($this->magentoAttributes);
        }

        return $this->magentoAttributes;
    }
}
