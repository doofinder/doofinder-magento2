<?php

namespace Doofinder\Feed\Test\Unit\Helper\Data;

/**
 * Class DataTest
 * @package Doofinder\Feed\Test\Unit\Helper\Data
 */
class FeedConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeInterfaceMock;
    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    protected $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_scopeInterfaceMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $feedAttributes = [
            'attr1' => 'value1',
            'attr2' => 'value2'
        ];

        $this->_scopeInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with('doofinder_feed_feed/feed_attributes')
            ->will($this->returnValue($feedAttributes));

        $this->_helper = $this->_objectManager->getObject(
            '\Doofinder\Feed\Helper\FeedConfig',
            [
                'scopeConfig'   => $this->_scopeInterfaceMock,
            ]
        );
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
                            'data' => [],
                        ],
                    ],
                    'processors' => [
                        'Mapper\Product' => [
                            'map' => [
                                'attr1' => 'value1',
                                'attr2' => 'value2',
                            ],
                        ],
                        'Cleaner' => [],
                        'Xml' => [],
                    ],
                ],
            ],
        ];

        $result = $this->_helper->getFeedConfig();

        $this->assertSame($expected, $result);
    }

    /**
     * Test set custom params.
     */
    public function testSetCustomParams()
    {
        $customParams = [
            'offset' => 1,
            'store' => 1,
            'minimal_price' => 20,
            'limit' => 10
        ];

        $expected = [
            'data' => [
                'config' => [
                    'fetchers' => [
                        'Product' => [
                            'data' => [
                                'offset' => 1,
                                'limit' => 10,
                            ],
                        ],
                    ],
                    'processors' => [
                        'Mapper\Product' => [
                            'map' => [
                                'store' => 1,
                                'minimal_price' => 20,
                                'attr1' => 'value1',
                                'attr2' => 'value2',
                            ],
                        ],
                        'Cleaner' => [],
                        'Xml' => [],
                    ],
                ],
            ],
        ];

        $this->_helper->setCustomParams($customParams);

        $result = $this->_helper->getFeedConfig();

        $this->assertSame($expected, $result);
    }
}
