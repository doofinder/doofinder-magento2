<?php

namespace Doofinder\Feed\Test\Unit\Model\Generator\Component\Processor;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater
 */
class AtomicUpdaterTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater
     */
    private $model;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item
     */
    private $item;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->item = $this->getMock(
            \Doofinder\Feed\Model\Generator\Item::class,
            [],
            [],
            '',
            false
        );

        $this->search = $this->getMock(
            \Doofinder\Feed\Helper\Search::class,
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Generator\Component\Processor\AtomicUpdater::class,
            [
                'search' => $this->search,
                'data' => [
                    'api_key' => 'sample_api_key',
                ],
            ]
        );
    }

    /**
     * Test process() method
     *
     * @return void
     */
    public function testProcess()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->item->method('getData')->willReturn($data);

        $this->search->expects($this->once())->method('updateDoofinderItems')
            ->with([$data]);

        $this->model->process([$this->item]);
    }
}
