<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Doofinder\Feed\Test\Unit\BaseTestCase;

class AtomicUpdaterTest extends BaseTestCase
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
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_item = $this->getMock(
            '\Doofinder\Feed\Model\Generator\Item',
            [],
            [],
            '',
            false
        );

        $this->_search = $this->getMock(
            '\Doofinder\Feed\Helper\Search',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater',
            [
                'search' => $this->_search,
                'data' => [
                    'api_key' => 'sample_api_key',
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

        $this->_search->expects($this->once())->method('updateDoofinderItems')
            ->with([$data]);

        $this->_model->process([$this->_item]);
    }
}
