<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Eav\Model\Entity\Attribute;

class Magento implements \Doofinder\Feed\Api\Data\FetcherInterface
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
     */
    public function process(array $documents, int $storeId)
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
     */
    public function get(int $productId): array
    {
        return $this->processed[$productId] ?? [];
    }

    /**
     * {@inheritDoc}
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
    private function convertToProductData(int $productId, array $indexData, int $storeId): array
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
            if (!$attribute->getIsSearchable() || $attribute->getIsUserDefined()) {
                continue;
            }
            if (!is_array($attributeValues)) {
                $attributeValues = [$productId => $attributeValues];
            }
            $attributeValues = $this->prepareAttributeValues($productId, $attribute, $attributeValues, $storeId);
            $attributeValue = $this->getAttributeValue($attribute, $attributeValues);
            $productAttributes[$attribute->getAttributeCode()] = $attributeValue;
        }

        return $productAttributes;
    }

    /**
     * Convert data for attribute, add {attribute_code}_value for searchable attributes, that contain actual value.
     *
     * @param Attribute $attribute
     * @param array $attributeValues
     * @return mixed|null
     */
    private function getAttributeValue(Attribute $attribute, array $attributeValues)
    {
        $attributeValue = null;
        $retrievedValue = $this->retrieveFieldValue($attributeValues);
        if ($retrievedValue) {
            $attributeValue = $retrievedValue;
            $attributeLabels = $this->getValuesLabels($attribute, $attributeValues);
            $retrievedLabel = $this->retrieveFieldValue($attributeLabels);
            if ($retrievedLabel) {
                $attributeValue = $retrievedLabel;
            }
        }

        return $attributeValue;
    }

    /**
     * Prepare attribute values.
     *
     * @param integer $productId
     * @param Attribute $attribute
     * @param array $attributeValues
     * @return array
     */
    private function prepareAttributeValues(int $productId, Attribute $attribute, array $attributeValues): array
    {
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
    private function prepareMultiselectValues(array $values): array
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
    private function isAttributeDate(Attribute $attribute): bool
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
    private function getValuesLabels(Attribute $attribute, array $attributeValues): array
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
    private function getAttributeOptions(Attribute $attribute): array
    {
        if (!isset($this->attrOptionsCache[$attribute->getId()])) {
            $options = $attribute->getOptions() ? $attribute->getOptions() : [];
            $this->attrOptionsCache[$attribute->getId()] = $options;
        }

        return $this->attrOptionsCache[$attribute->getId()];
    }

    /**
     * Retrieve value for field. If field have only one value this method return it.
     * Otherwise, will be returned array of these values.
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
