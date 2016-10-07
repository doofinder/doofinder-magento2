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
     * @var \SearchEngine
     */
    private $_searchEngine;

    /**
     * @var \DoofinderManagementApi
     */
    private $_dma;

    /**
     * @var \Doofinder\Api\Management\ClientFactory
     */
    private $_dmaFactory;

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

        $this->_searchEngine = $this->getMock(
            '\Doofinder\Api\Management\SearchEngine',
            [],
            [],
            '',
            false
        );

        $this->_dma = $this->getMock(
            '\Doofinder\Api\Management\Client',
            [],
            [],
            '',
            false
        );
        $this->_dma->method('getSearchEngines')->willReturn([$this->_searchEngine]);

        $this->_dmaFactory = $this->getMock(
            '\Doofinder\Api\Management\ClientFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_dmaFactory->expects($this->once())->method('create')
            ->with('sample_api_key')->willReturn($this->_dma);

        $this->_model = $this->_objectManagerHelper->getObject(
            '\Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater',
            [
                'dmaFactory' => $this->_dmaFactory,
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

        $this->_searchEngine->expects($this->once())->method('updateItems')
            ->with('product', [$data]);

        $this->_model->process([$this->_item]);
    }
}
