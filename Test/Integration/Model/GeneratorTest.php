<?php

namespace Doofinder\Feed\Test\Integration\Model;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Model\Generator
 *
 * @codingStandardsIgnoreStart
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @codingStandardsIgnoreEnd
 */
class GeneratorTest extends AbstractIntegrity
{
    /**
     * Test run() method
     *
     * @return void
     */
    public function testRun()
    {
        $generator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Doofinder\Feed\Model\Generator::class,
            [
                'data' => [
                    'config' => [
                        'fetchers' => [
                            'Product' => []
                        ],
                        'processors' => [
                            'Mapper' => [
                                'map' => [
                                    'title' => 'name',
                                    'description' => 'short_description',
                                    'price' => 'price',
                                    'mpn' => 'sku',
                                ],
                                'export_product_prices' => 1,
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
<title><![CDATA[Product feed]]></title>
<link><![CDATA[%s/doofinder/feed]]></link>
<pubDate><![CDATA[%s, %d %s %d %d:%d:%d UTC]]></pubDate>
<generator><![CDATA[Doofinder/%d.%d.%d]]></generator>
<description><![CDATA[Magento Product feed for Doofinder]]></description>
<item>
 <title><![CDATA[Simple Product]]></title>
 <description><![CDATA[Short description]]></description>
 <price><![CDATA[10]]></price>
 <mpn><![CDATA[simple]]></mpn>
</item>
<item>
 <title><![CDATA[Virtual Product]]></title>
 <price><![CDATA[10]]></price>
 <mpn><![CDATA[virtual-product]]></mpn>
</item>
</channel>
</rss>

EOT;

        $this->assertStringMatchesFormat($expected, $generator->getProcessor('Xml')->getFeed());
    }
}
