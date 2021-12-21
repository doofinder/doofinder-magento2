<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update\Fetcher;

use Doofinder\Feed\Model\Indexer\Data\Map\Update\FetcherInterface;
use Exception;
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
     * storeConfig
     *
     * @var mixed
     */
    private $storeConfig;

    /**
     * @param \Doofinder\Feed\Helper\StoreConfig $storeConfig
     * @param DataProvider $dataProvider
     * @param array $excludedAttributes
     */
    public function __construct(
        \Doofinder\Feed\Helper\StoreConfig $storeConfig,
        DataProvider                       $dataProvider,
        array                              $excludedAttributes = []
    )
    {
        $this->dataProvider = $dataProvider;
        $this->excludedAttributes = $excludedAttributes;
        $this->storeConfig = $storeConfig;
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
            $attributeValues = $this->prepareAttributeValues($productId, $attribute, $attributeValues);
            $productAttributes += $this->convertAttribute($attribute, $attributeValues);
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
    )
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
            //check the input type
            foreach ($attributeValues as $key => $attributeValue) {
                $attributeValues[$key] = $attributeValue;
            }
        }

        if ($this->checkIfConvertible($attribute)) {
            foreach ($attributeValues as $key => $attributeValue) {
                $attributeValues[$key] = $this->changeType('int', $attributeValue);
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
     * checkIfConvertible
     *
     * @param mixed $attribute
     * @return bool
     */

    private function checkIfConvertible($attribute)
    {
        try {
            if (in_array($attribute->getAttributeCode(), $this->storeConfig->getAttributesForConversion(), true)) {
                return true;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * changeType
     *
     * @param mixed $type
     * @param mixed $value
     * @return void
     */

    public function changeType($type, $value)
    {
        $converted = null;
        switch ($type) {
            case 'int':
                try {
                    $converted = (int)$value;
                } catch (Exception $ex) {
                    $converted = $value;
                }
                break;

        }
        return $converted;
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
     * {@inheritDoc}
     * @param integer $productId
     * @return array
     */
    public function get($productId)
    {
        return $this->processed[$productId] ?? [];
    }
}
