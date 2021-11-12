<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\Serializer
 */
class SerializerTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
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
    protected function setupTests()
    {
        $serializer = $this->objectManager->getObject(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\Serializer::class,
            ['serializer' => $serializer]
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
        $array = ['hello', 'world'];
        $serialized = json_encode($array);
        return [
            [$array, $serialized]
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
