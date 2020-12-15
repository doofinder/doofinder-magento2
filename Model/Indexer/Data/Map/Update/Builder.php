<?php

namespace Doofinder\Feed\Model\Indexer\Data\Map\Update;

use Magento\Framework\Phrase;

/**
 * Class Builder
 * The class responsible for building product data for index
 */
class Builder
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @return array
     */
    public function build()
    {
        $document = [];
        foreach ($this->fields as $field => $value) {
            // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $document = array_merge(
                $document,
                $this->getField($field, $value)
            );
            // phpcs:enable
        }
        $this->clear();
        return $document;
    }

    /**
     * @return void
     */
    private function clear()
    {
        $this->fields = [];
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return array
     */
    private function getField($field, $value)
    {
        if (is_array($value)) {
            if (count($value) == 0) {
                return [$field => $value];
            }

            $fields = [];
            foreach ($value as $val) {
                $val = $this->filterValue($val);
                if (!$val) {
                    continue;
                }
                $fields[$field][] = $this->filterValue($val);
            }
            return $fields;
        }

        $value = $this->filterValue($value);
        return [$field => $value];
    }

    /**
     * @param mixed $val
     * @return mixed
     */
    private function filterValue($val)
    {
        if ($val instanceof Phrase) {
            // Make sure that Phrase object is converted into string
            return $val->render();
        }
        if (is_array($val) || is_object($val) || is_resource($val)) {
            // Do not try to index multidimensional arrays/objects/resources
            return null;
        }
        return $val;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addField($field, $value)
    {
        $this->fields[$field] = $value;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function addFields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }
}
