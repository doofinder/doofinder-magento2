<?php
declare(strict_types=1);


namespace Doofinder\Feed\Model\Indexer\Data\Map\Update;

class Builder
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @return array
     */
    public function build(): array
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
    private function getField(string $field, $value): array
    {
        if (is_array($value)) {
            if (count($value) == 0) {
                return [$field => $value];
            }
            $fields = [];
            foreach ($value as $key => $val) {
                $fields[$field][$key] = $val;
            }
            return $fields;
        }

        return [$field => $value];
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function addField(string $field, $value): Builder
    {
        $this->fields[$field] = $value;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function addFields(array $fields): Builder
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }
}
