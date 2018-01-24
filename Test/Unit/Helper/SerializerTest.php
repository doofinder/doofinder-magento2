<?php

namespace Doofinder\Feed\Test\Unit\Helper;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Helper\Serializer
 */
class SerializerTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\Serializer
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

        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\Serializer::class
        );
    }

    /**
     * Test serialize() method.
     *
     * @param  string $value
     * @param  string $expected
     * @return void
     * @dataProvider serializeTestProvider
     */
    public function testSerialize($value, $expected)
    {
        $this->assertSame($expected, $this->helper->serialize($value));
    }

    /**
     * Data provider for testSerialize() test
     *
     * @return array
     */
    public function serializeTestProvider()
    {
        return [
            ['string', 's:6:"string";'],
            [['hello', 'world'], 'a:2:{i:0;s:5:"hello";i:1;s:5:"world";}'],
        ];
    }

    /**
     * Test unserialize() method.
     *
     * @param  string $expected
     * @param  string $value
     * @return void
     * @dataProvider serializeTestProvider
     */
    public function testUnserialize($expected, $value)
    {
        $this->assertSame($expected, $this->helper->unserialize($value));
    }
}
