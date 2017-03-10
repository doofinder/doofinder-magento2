<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Class FeedConfigTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class FeedConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\FeedConfig
     */
    protected $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_storeConfig = $this->getMock(
            '\Doofinder\Feed\Helper\StoreConfig',
            [],
            [],
            '',
            false
        );

        $this->_storeConfig->method('getStoreConfig')->willReturn([
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

        $this->_helper = $this->_objectManager->getObject(
            '\Doofinder\Feed\Helper\FeedConfig',
            [
                'storeConfig'   => $this->_storeConfig,
            ]
        );
    }

    /**
     * Test get lean feed config.
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
                        'Cleaner' => [],
                    ],
                ],
            ],
        ];

        $result = $this->_helper->getLeanFeedConfig();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test get feed config.
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
                        'Cleaner' => [],
                        'Xml' => [],
                    ],
                ],
            ],
        ];

        $result = $this->_helper->getFeedConfig();

        $this->assertEquals($expected, $result);
    }

    /**
     * Test set custom params.
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
                        'Cleaner' => [],
                        'Xml' => [],
                    ],
                ],
            ],
        ];

        $result = $this->_helper->getFeedConfig(null, $customParams);

        $this->assertEquals($expected, $result);
    }
}
