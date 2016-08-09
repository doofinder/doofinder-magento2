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

        $this->_helper = $this->_objectManager->getObject(
            '\Doofinder\Feed\Helper\FeedConfig',
            [
                'scopeConfig'   => $this->_scopeInterfaceMock,
            ]
        );
    }

    public function testGetFeedConfig()
    {
        $feedAttributes = [
            'attr1' => 'value1',
            'attr2' => 'value2'
        ];

        $this->_scopeInterfaceMock->expects($this->once())
            ->method('getValue')
            ->with('doofinder_feed_feed/feed_attributes')
            ->will($this->returnValue($feedAttributes));

        $expected = [
            'data' => [
                'config' => [
                    'fetchers' => [
                        'Product' => []
                    ],
                    'processors' => [
                        'Mapper\Product' => [
                            'map' => $feedAttributes
                        ],
                        'Cleaner' => [],
                        'Xml' => []
                    ]
                ]
            ]
        ];

        $result = $this->_helper->getFeedConfig();

        $this->assertSame($expected, $result);
    }
}
