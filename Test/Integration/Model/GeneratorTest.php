<?php

namespace Doofinder\Feed\Test\Integration\Model;

/**
 * Test class for \Doofinder\Feed\Model\Generator
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
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
                            'Mapper' => [
                                'map' => [
                                    'id' => 'sku',
                                    'name' => 'name',
                                    'price' => 'price',
                                    'url' => 'url_key',
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
  <id>simple</id>
  <name>Simple Product With Custom Options</name>
  <price>10.0000</price>
  <url>simple-product-with-custom-options</url>
 </item>
</feed>

EOT;

        $this->assertEquals($expected, $generator->getProcessor('Xml')->getFeed());
    }
}
