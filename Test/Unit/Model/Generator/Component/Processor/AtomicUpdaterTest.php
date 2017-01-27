<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

class AtomicUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater
     */
    private $_model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $_item;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $_search;

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

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );

        $this->_search = $this->getMock(
            '\Doofinder\Feed\Helper\Search',
            ['someActionDoofinderItems'],
            [],
            '',
            false
        );

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater',
            [
                'search' => $this->_search,
                'data' => [
                    'api_key' => 'sample_api_key',
                    'action' => 'someAction',
                ],
            ]
        );
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->_item->method('getData')->willReturn($data);

        $this->_search->expects($this->once())->method('someActionDoofinderItems')
            ->with([$data]);

        $this->_model->process([$this->_item]);
    }
}
