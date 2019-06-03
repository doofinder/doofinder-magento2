<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Doofinder\Feed\Test\Integration\Controller\Feed;

use \Sabre\Xml\Service as XmlService;

/**
 * Test class for \Doofinder\Feed\Controller\Feed\Index.
 *
 * @class IndexTest
 * @magentoDataFixture products
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    const XML_NAMESPACE = '{}';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $url = $this->_objectManager->get(\Magento\Framework\UrlInterface::class);
        $this->baseUrl = $url->getBaseUrl([
            '_type' => \Magento\Framework\UrlInterface::URL_TYPE_LINK,
        ]);
    }

    /**
     * Products fixture
     *
     * @return void
     */
    public static function products()
    {
        // @codingStandardsIgnoreStart
        require __DIR__ . '/../../_files/products.php';
        // @codingStandardsIgnoreEnd
    }

    /**
     * Test for index action
     *
     * @return void
     */
    public function testIndexAction()
    {
        $this->dispatch('doofinder/feed');

        // Check if XML was returned
        $this->assertHeaderPcre('Content-Type', '/application\/xml/');

        $expectedItems = [
            [
                'id' => 1,
                'title' => 'Product 1',
                'description' => 'Standard product',
                'link' => $this->baseUrl . 'product-1.html',
                'price' => 10,
                'mpn' => 'product-1',
                'availability' => 'in stock',
                'categories' => 'Category 5',
            ],
            [
                'id' => 3,
                'title' => 'Product 3',
                'description' => 'Product visible in search only',
                'link' => $this->baseUrl . 'product-3.html',
                'price' => 10,
                'mpn' => 'product-3',
                'availability' => 'in stock',
                'categories' => 'Category 5',
            ],
            [
                'id' => 6,
                'title' => 'Product 6',
                'description' => 'Product out of stock',
                'link' => $this->baseUrl . 'product-6.html',
                'price' => 10,
                'mpn' => 'product-6',
                'availability' => 'out of stock',
                'categories' => 'Category 5',
            ],
            [
                'id' => 7,
                'title' => 'Product 7',
                'description' => 'With both active and inactive category',
                'link' => $this->baseUrl . 'product-7.html',
                'price' => 10,
                'mpn' => 'product-7',
                'availability' => 'in stock',
                'categories' => 'Category 4',
            ],
            [
               'id' => 8,
               'title' => 'Product 8',
               'description' => 'With category having inactive parent',
               'link' => $this->baseUrl . 'product-8.html',
               'price' => 10,
               'mpn' => 'product-8',
               'availability' => 'in stock',
            ],
            [
               'id' => 9,
               'title' => 'Product 9',
               'description' => 'With category having inactive ancestor',
               'link' => $this->baseUrl . 'product-9.html',
               'price' => 10,
               'mpn' => 'product-9',
               'availability' => 'in stock',
            ],
        ];

        $data = $this->parseXml($this->getResponse()->getContent());
        $items = $this->extractItems($data);

        $this->assertEquals($expectedItems, $items);
    }

    /**
     * Parse XML
     *
     * @param string $xml
     * @return array
     */
    private function parseXml($xml)
    {
        $service = new XmlService();
        $service->elementMap = [
            'rss' => 'Sabre\Xml\Deserializer\keyValue',
            'item' => 'Sabre\Xml\Deserializer\keyValue',
        ];
        return $service->parse($xml);
    }

    /**
     * Extract items from xml data
     *
     * @param array $data
     * @return array
     */
    private function extractItems(array $data)
    {
        // Filter out items
        $items = array_filter($data[self::XML_NAMESPACE . 'channel'], function ($row) {
            return $row['name'] == self::XML_NAMESPACE . 'item';
        });

        // Return converted items to simple key => value array
        return array_map(function ($item) {
            return array_flip(
                array_map(function ($key) {
                    return str_replace(self::XML_NAMESPACE, '', $key);
                }, array_flip($item['value']))
            );
        }, array_values($items));
    }
}
