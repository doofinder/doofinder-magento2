<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor;

use \Doofinder\Feed\Model\Generator\Component;
use \Doofinder\Feed\Model\Generator\Component\ProcessorInterface;

class Cleaner extends Component implements ProcessorInterface
{
    /**
     * Process items
     *
     * @param \Doofinder\Feed\Model\Generator\Item[]
     */
    public function process(array $items)
    {
        foreach ($items as $item) {
            $this->processItem($item);
        }
    }

    /**
     * Process item
     *
     * @param \Doofinder\Feed\Model\Generator\Item
     */
    private function processItem(\Doofinder\Feed\Model\Generator\Item $item)
    {
        foreach ($item->getData() as $key => $value) {
            $item->setData($key, $this->clean($value));
        }
    }

    /**
     * Clean field
     *
     * @param string|array
     * @return string|array
     */
    private function clean($field)
    {
        // Do nothing if field is empty
        if (!$field) {
            return $field;
        }

        $cleaned = array_map([$this, 'cleanString'], (array) $field);
        return is_array($field) ? $cleaned : $cleaned[0];
    }

    /**
     * Clean string
     *
     * @param string
     * @return string
     */
    public function cleanString($field)
    {
        $field = $this->cleanInvalidUTF($field);
        $field = $this->cleanNewlines($field);
        $field = $this->cleanTags($field);
        $field = $this->cleanDuplicatedSpaces($field);
        $field = $this->trim($field);
        $field = $this->decodeHtmlEntities($field);

        return $field;
    }

    /**
     * Clean new lines
     *
     * @param string
     * @return string
     */
    private function cleanNewlines($field)
    {
        return preg_replace('#<br(\s?/)?>#i', ' ', $field);
    }

    /**
     * Clean tags
     *
     * @param string
     * @return string
     */
    private function cleanTags($field)
    {
        return strip_tags($field);
    }

    /**
     * Clean duplicated spaces
     *
     * @param string
     * @return string
     */
    private function cleanDuplicatedSpaces($field)
    {
        return preg_replace('/[ ]{2,}/', ' ', $field);
    }

    /**
     * Trim
     *
     * @param string
     * @return string
     */
    private function trim($field)
    {
        return trim($field);
    }

    /**
     * Decode HTML entities
     *
     * @param string
     * @return string
     */
    private function decodeHtmlEntities($field)
    {
        // @codingStandardsIgnoreStart
        return html_entity_decode($field, null, 'UTF-8');
        // @codingStandardsIgnoreEnd
    }

    /**
     * Clean invalid UTF-8 characters
     *
     * @see http://stackoverflow.com/questions/4224141/php-removing-invalid-utf-8-characters-in-xml-using-filter
     *
     * @param string
     * @return string
     */
    private function cleanInvalidUTF($field)
    {
        $validUtf = '/([\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF' .
                     '][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF' .
                     ']{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})|./x';

        return preg_replace($validUtf, '$1', $field);
    }
}
