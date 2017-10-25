<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

class ItemTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Generator\Item',
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
     * Test xmlSerialize
     */
    public function testXmlSerialize()
    {
        // @codingStandardsIgnoreStart
        $service = new \Sabre\Xml\Service();
        // @codingStandardsIgnoreEnd
        $output = $service->write('feed', $this->_model);

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
