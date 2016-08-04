<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_model = $this->objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Item',
            [
                'data' => [
                    'title' => 'Sample product',
                    'price' => 20.99,
                    'description' => 'Lorem ipsum dolor sit amet',
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
  <title>Sample product</title>
  <price>20.99</price>
  <description>Lorem ipsum dolor sit amet</description>
 </item>
</feed>

EOT;

        $this->assertEquals($expected, $output);
    }
}
