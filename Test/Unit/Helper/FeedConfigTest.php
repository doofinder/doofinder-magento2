<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Helper\FeedConfig
 */
class FeedConfigTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->storeConfig = $this->getMock(
            \Doofinder\Feed\Helper\StoreConfig::class,
            [],
            [],
            '',
            false
        );

        $this->storeConfig->method('getStoreConfig')->willReturn([
            'store_code' => 'default',
            'attributes' => [
                'attr1' => 'value1',
                'attr2' => 'value2',
            ],
            'image_size' => 'small',
            'split_configurable_products' => 1,
            'export_product_prices' => 0,
            'price_tax_mode' => 0,
            'categories_in_navigation' => 0,
        ]);

        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\FeedConfig::class,
            [
                'storeConfig'   => $this->storeConfig,
            ]
        );
    }

    /**
     * Test getLeanFeedConfig() method
     *
     * @return void
     */
    public function testGetLeanFeedConfig()
    {
        $expected = [
            'data' => [
                'config' => [
                    'fetchers' => [],
                    'processors' => [
                        'Mapper' => [
                            'map' => [
                                'attr1' => 'value1',
                                'attr2' => 'value2',
                            ],
                            'split_configurable_products' => 1,
                            'export_product_prices' => 0,
                            'price_tax_mode' => 0,
                            'image_size' => 'small',
                            'categories_in_navigation' => 0,
                        ],
                        'Filter' => [],
                        'Cleaner' => [],
                    ],
                ],
            ],
        ];

        $result = $this->helper->getLeanFeedConfig();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getFeedConfig() method
     *
     * @return void
     */
    public function testGetFeedConfig()
    {
        $expected = [
            'data' => [
                'config' => [
                    'fetchers' => [
                        'Product' => [
                            'offset' => null,
                            'limit' => null,
                        ],
                    ],
                    'processors' => [
                        'Mapper' => [
                            'map' => [
                                'attr1' => 'value1',
                                'attr2' => 'value2',
                            ],
                            'split_configurable_products' => 1,
                            'export_product_prices' => 0,
                            'price_tax_mode' => 0,
                            'image_size' => 'small',
                            'categories_in_navigation' => 0,
                        ],
                        'Filter' => [],
                        'Cleaner' => [],
                        'Xml' => [],
                    ],
                ],
            ],
        ];

        $result = $this->helper->getFeedConfig();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test getFeedConfig() method with custom parameters
     *
     * @return void
     */
    public function testGetFeedConfigWithCustomParams()
    {
        $customParams = [
            'offset' => 1,
            'store' => 1,
            'limit' => 10
        ];

        $expected = [
            'data' => [
                'config' => [
                    'fetchers' => [
                        'Product' => [
                            'offset' => 1,
                            'limit' => 10,
                        ],
                    ],
                    'processors' => [
                        'Mapper' => [
                            'map' => [
                                'attr1' => 'value1',
                                'attr2' => 'value2',
                            ],
                            'split_configurable_products' => 1,
                            'export_product_prices' => 0,
                            'price_tax_mode' => 0,
                            'image_size' => 'small',
                            'categories_in_navigation' => 0,
                        ],
                        'Filter' => [],
                        'Cleaner' => [],
                        'Xml' => [],
                    ],
                ],
            ],
        ];

        $result = $this->helper->getFeedConfig(null, $customParams);

        $this->assertEquals($expected, $result);
    }
}
