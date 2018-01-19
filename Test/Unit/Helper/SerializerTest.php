<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class SerializerTest
 * @package Doofinder\Feed\Test\Unit\Helper
 */
class SerializerTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Serializer
     */
    private $_helper;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_helper = $this->objectManager->getObject(
            '\Doofinder\Feed\Helper\Serializer'
        );
    }

    /**
     * Test serialize() method.
     *
     * @dataProvider serializeProvider
     */
    public function testSerialize($value, $expected)
    {
        $this->assertSame($expected, $this->_helper->serialize($value));
    }

    public function serializeProvider()
    {
        return [
            ['string', 's:6:"string";'],
            [['hello', 'world'], 'a:2:{i:0;s:5:"hello";i:1;s:5:"world";}'],
        ];
    }

    /**
     * Test unserialize() method.
     *
     * @dataProvider serializeProvider
     */
    public function testUnserialize($expected, $value)
    {
        $this->assertSame($expected, $this->_helper->unserialize($value));
    }
}
