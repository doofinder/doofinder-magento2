<?php

namespace Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log\Column\Type;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class OptionsTest
 * @package Doofinder\Feed\Test\Unit\Ui\Component\Listing\Log\Column\Type
 */
class OptionsTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Logger\Feed
     */
    private $_logger;

    /**
     * @var \Doofinder\Feed\Ui\Component\Listing\Log\Column\Type\Options
     */
    private $_options;

    public function setUp()
    {
        parent::setUp();

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

        $this->_options = $this->objectManager->getObject(
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

        $this->assertEquals(
            $expected,
            $this->_options->toOptionArray()
        );
    }
}
