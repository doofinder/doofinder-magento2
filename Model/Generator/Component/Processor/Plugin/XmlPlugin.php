<?php

namespace Doofinder\Feed\Model\Generator\Component\Processor\Plugin;

/**
 * XmlPlugin
 */
class XmlPlugin
{
    /**
     * Prepare items before generating the feed
     *
     * @param \Doofinder\Feed\Model\Generator\Component\Processor\Xml $subject
     * @param \Doofinder\Feed\Model\Generator\Item[] $items
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function beforeProcess(
        \Doofinder\Feed\Model\Generator\Component\Processor\Xml $subject,
        array $items
    ) {
    // @codingStandardsIgnoreEnd
        foreach ($items as $item) {
            /**
             * Stringifies tree
             * example: Category 1>Category 1.1%%Category 2>Category 2.1>Category 2.1.1
             */
            $categories = $item->getCategories();
            if (is_array($categories)) {
                $item->setCategories(
                    implode(
                        \Doofinder\Feed\Model\Generator::CATEGORY_SEPARATOR,
                        $categories
                    )
                );
            }
        }
    }
}
