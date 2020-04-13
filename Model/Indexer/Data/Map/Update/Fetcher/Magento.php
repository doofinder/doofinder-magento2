<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class Magento
 * The class responsible for providing Magento attributes to index
 */
class Magento implements FetcherInterface
{
    /**
     * @var string[]
     */
    private $attrsExcluded = [
        'status',
        'visibility',
        'tax_class_id'
    ];

    /**
     * @var array
     */
    private $excludedAttributes;

    /**
     * @var array
     */
    private $attrOptionsCache;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var array|null
     */
    private $processed;

    /**
     * Magento constructor.
     * @param DataProvider $dataProvider
     * @param array $excludedAttributes
     */
    public function __construct(
        DataProvider $dataProvider,
        array $excludedAttributes = []
    ) {
        $this->dataProvider = $dataProvider;
        $this->excludedAttributes = $excludedAttributes;
    }

    /**
     * {@inheritDoc}
     * @param array $documents
     * @param integer $storeId
     * @return void
     */
    public function process(array $documents, $storeId)
    {
        $this->clear();
        foreach ($documents as $productId => $indexData) {
            $productIndexData = $this->convertToProductData($productId, $indexData, $storeId);

            foreach ($productIndexData as $attributeCode => $value) {
                $this->processed[$productId][$attributeCode] = $value;
            }
        }
    }

    /**
     * {@inheritDoc}
     * @param integer $productId
     * @return array
     */
    public function get($productId)
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * {@inheritDoc}
     * @return void
     */
    public function clear()
    {
        $this->processed = [];
    }

    /**
     * Convert raw data retrieved from source tables to human-readable format.
     *
     * @param integer $productId
     * @param array $indexData
     * @param integer $storeId
     * @return array
     */
    private function convertToProductData($productId, array $indexData, $storeId)
    {
        $productAttributes = [];

        if (isset($indexData['options'])) {
            $productAttributes['options'] = $indexData['options'];
            unset($indexData['options']);
        }

        foreach ($indexData as $attributeId => $attributeValues) {
            $attribute = $this->dataProvider->getSearchableAttribute($attributeId);
            if (in_array($attribute->getAttributeCode(), $this->excludedAttributes, true)) {
                continue;
            }

            if (!is_array($attributeValues)) {
                $attributeValues = [$productId => $attributeValues];
            }
            $attributeValues = $this->prepareAttributeValues($productId, $attribute, $attributeValues, $storeId);
            $productAttributes += $this->convertAttribute($attribute, $attributeValues);
        }

        return $productAttributes;
    }

    /**
     * Convert data for attribute, add {attribute_code}_value for searchable attributes, that contain actual value.
     *
     * @param Attribute $attribute
     * @param array $attributeValues
     * @return array
     */
    private function convertAttribute(Attribute $attribute, array $attributeValues)
    {
        $productAttributes = [];

        $retrievedValue = $this->retrieveFieldValue($attributeValues);
        if ($retrievedValue) {
            $productAttributes[$attribute->getAttributeCode()] = $retrievedValue;

            if ($attribute->getIsSearchable()) {
                $attributeLabels = $this->getValuesLabels($attribute, $attributeValues);
                $retrievedLabel = $this->retrieveFieldValue($attributeLabels);
                if ($retrievedLabel) {
                    $productAttributes[$attribute->getAttributeCode() . '_value'] = $retrievedLabel;
                }
            }
        }

        return $productAttributes;
    }

    /**
     * Prepare attribute values.
     *
     * @param integer $productId
     * @param Attribute $attribute
     * @param array $attributeValues
     * @return array
     */
    private function prepareAttributeValues(
        $productId,
        Attribute $attribute,
        array $attributeValues
    ) {
        if (in_array($attribute->getAttributeCode(), $this->attrsExcluded, true)) {
            $attributeValues = [
                $productId => $attributeValues[$productId] ?? '',
            ];
        }

        if ($attribute->getFrontendInput() === 'multiselect') {
            $attributeValues = $this->prepareMultiselectValues($attributeValues);
        }

        if ($this->isAttributeDate($attribute)) {
            foreach ($attributeValues as $key => $attributeValue) {
                $attributeValues[$key] = $attributeValue;
            }
        }

        return $attributeValues;
    }

    /**
     * Prepare multiselect values.
     *
     * @param array $values
     * @return array
     */
    private function prepareMultiselectValues(array $values)
    {
        return array_merge(...array_map(function ($value) {
            return explode(',', $value);
        }, $values));
    }

    /**
     * Is attribute date.
     *
     * @param Attribute $attribute
     * @return boolean
     */
    private function isAttributeDate(Attribute $attribute)
    {
        return $attribute->getFrontendInput() === 'date'
            || in_array($attribute->getBackendType(), ['datetime', 'timestamp'], true);
    }

    /**
     * Get values labels.
     *
     * @param Attribute $attribute
     * @param array $attributeValues
     * @return array
     */
    private function getValuesLabels(Attribute $attribute, array $attributeValues)
    {
        $attributeLabels = [];

        $options = $this->getAttributeOptions($attribute);
        if (empty($options)) {
            return $attributeLabels;
        }

        foreach ($options as $option) {
            if (in_array($option->getValue(), $attributeValues)) {
                $attributeLabels[] = $option->getLabel();
            }
        }

        return $attributeLabels;
    }

    /**
     * Retrieve options for attribute
     *
     * @param Attribute $attribute
     * @return array
     */
    private function getAttributeOptions(Attribute $attribute)
    {
        if (!isset($this->attrOptionsCache[$attribute->getId()])) {
            $options = $attribute->getOptions() ? $attribute->getOptions() : [];
            $this->attrOptionsCache[$attribute->getId()] = $options;
        }

        return $this->attrOptionsCache[$attribute->getId()];
    }

    /**
     * Retrieve value for field. If field have only one value this method return it.
     * Otherwise will be returned array of these values.
     * Note: array of values must have index keys, not as associative array.
     *
     * @param array $values
     * @return array|string
     */
    private function retrieveFieldValue(array $values)
    {
        $values = array_filter(array_unique($values));

        return count($values) === 1 ? array_shift($values) : array_values($values);
    }
}
