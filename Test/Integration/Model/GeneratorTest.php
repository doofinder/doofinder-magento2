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
<?xml version="1.0"?>
<feed>
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
</feed>

EOT;

        $this->assertEquals($expected, $generator->getProcessor('Xml')->getFeed());
    }
}
