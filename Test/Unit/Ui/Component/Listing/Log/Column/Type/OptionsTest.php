<?php

namespace Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log\Column\Type;

/**
 * Class OptionsTest
 * @package Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log\Column\Type
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    protected $_logger;

    /**
     * @var \Doofinder\Feed\Ui\Component\Listing\Log\Column\Type\Options
     */
    protected $_options;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_logger = $this->getMock(
            '\Doofinder\Feed\Logger\Feed',
            [],
            [],
            '',
            false
        );
        $this->_logger->method('getLevelOptions')->willReturn([
            100 => 'DEBUG',
            200 => 'INFO',
            300 => 'WARNING',
            400 => 'ERROR',
        ]);

        $this->_options = $this->_objectManager->getObject(
            '\Doofinder\Feed\Ui\Component\Listing\Log\Column\Type\Options',
            [
                'logger' => $this->_logger,
            ]
        );
    }

    /**
     * Test toOptionArray()
     */
    public function testToOptionArray()
    {
        $expected = [
            ['label' => 'debug', 'value' => 'debug'],
            ['label' => 'info', 'value' => 'info'],
            ['label' => 'warning', 'value' => 'warning'],
            ['label' => 'error', 'value' => 'error'],
        ];

        $this->_options->toOptionArray();
    }
}
