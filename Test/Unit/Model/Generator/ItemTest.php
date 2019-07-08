<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Item
 */
class ItemTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $model;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Item::class,
            [
                'data' => [
                    'name' => 'Sample product',
                    'price' => 20.99,
                    'description' => 'Lorem ipsum dolor sit amet',
                    'nested' => [
                        'cookie' => 'muffin',
                        'juice' => 'apple',
                    ],
                ],
            ]
        );
    }

    /**
     * Test xmlSerialize() method
     *
     * @return void
     */
    public function testXmlSerialize()
    {
        // @codingStandardsIgnoreStart
        $service = new \Sabre\Xml\Service();
        // @codingStandardsIgnoreEnd
        $output = $service->write('feed', $this->model);

        $expected = <<<EOT
<?xml version="1.0"?>
<feed>
 <item>
  <name><![CDATA[Sample product]]></name>
  <price><![CDATA[20.99]]></price>
  <description><![CDATA[Lorem ipsum dolor sit amet]]></description>
  <nested>
   <cookie><![CDATA[muffin]]></cookie>
   <juice><![CDATA[apple]]></juice>
  </nested>
 </item>
</feed>

EOT;

        $this->assertEquals($expected, $output);
    }
}
