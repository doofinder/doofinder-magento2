<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $_objectManagerHelper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_model = $this->_objectManagerHelper->getObject(
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
        $service = new \Sabre\Xml\Service();
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
