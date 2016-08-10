<?php

namespace Doofinder\Feed\Test\Integration\Model;

/**
 * Test class for \Doofinder\Feed\Model\Generator
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $generator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Doofinder\Feed\Model\Generator',
            [
                'data' => [
                    'config' => [
                        'fetchers' => [
                            'Product' => []
                        ],
                        'processors' => [
                            'Mapper\Product' => [
                                'map' => [
                                    'title' => 'name',
                                    'description' => 'short_description',
                                    'price' => 'price',
                                    'mpn' => 'sku',
                                ]
                            ],
                            'Cleaner' => [],
                            'Xml' => []
                        ]
                    ]
                ]
            ]
        );

        $generator->run();

        $expected = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
<title>Product feed</title>
<link>%s/doofinder/feed</link>
<pubDate>%s, %d %s %d %d:%d:%d UTC</pubDate>
<generator>Doofinder/%d.%d.%d</generator>
<description>Magento Product feed for Doofinder</description>
<item>
 <title>Simple Product</title>
 <description>Short description</description>
 <price>10.00</price>
 <mpn>simple</mpn>
</item>
<item>
 <title>Virtual Product</title>
 <price>10.00</price>
 <mpn>virtual-product</mpn>
</item>
</channel>
</rss>

EOT;

        $this->assertStringMatchesFormat($expected, $generator->getProcessor('Xml')->getFeed());
    }
}
