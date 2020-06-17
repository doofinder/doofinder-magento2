<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\HashIdValidation
 */
class HashIdValidationTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Helper\Search
     */
    private $search;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private $resource;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\HashIdValidation
     */
    private $model;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->storeConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\StoreConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeConfig->method('getCurrentStoreCode')->willReturn('current');
        $this->storeConfig->method('getStoreCodes')->willReturn(['sample1', 'current', 'sample2']);
        $this->storeConfig->method('getHashId')->will($this->returnValueMap([
            ['current', 'sample_current_hash_id'],
            ['sample1', 'sample_hash_id_1'],
            ['sample2', 'sample_hash_id_2'],
        ]));

        $this->search = $this->getMockBuilder(\Doofinder\Feed\Helper\Search::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\HashIdValidation::class,
            [
                'storeConfig' => $this->storeConfig,
                'search' => $this->search,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * Test save() method
     *
     * @return void
     */
    public function testSave()
    {
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->search->method('getDoofinderSearchEngines')->with('some-api-key')->willReturn([
            'sample_hash_id' => [],
        ]);

        $this->model->setValue('sample_hash_id');
        $this->model->save();
    }

    /**
     * Test save() method with empty hash id
     *
     * @return void
     */
    public function testSaveEmpty()
    {
        $this->resource->expects($this->once())->method('save');

        $this->model->setValue(null);
        $this->model->save();
    }

    /**
     * Test save() with empty hash id with engine enabled
     *
     * @return void
     */
    public function testSaveEmtpyEngineEnabled()
    {
        $this->resource->expects($this->never())->method('save');
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(true);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->model->setValue(null);
        $this->model->save();
    }

    /**
     * Test save() method
     *
     * @return void
     */
    public function testSaveSameAsCurrent()
    {
        $this->resource->expects($this->once())->method('save');

        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->search->method('getDoofinderSearchEngines')->with('some-api-key')->willReturn([
            'sample_current_hash_id' => [],
        ]);

        $this->model->setValue('sample_current_hash_id');
        $this->model->save();
    }

    /**
     * Test save() method
     *
     * @return void
     */
    public function testSaveNotUnique()
    {
        $this->resource->expects($this->never())->method('save');

        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->search->method('getDoofinderSearchEngines')->with('some-api-key')->willReturn([
            'sample_hash_id_2' => [],
        ]);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->model->setValue('sample_hash_id_2');
        $this->model->save();
    }

    /**
     * Test save() method with unavailable engine
     *
     * @return void
     */
    public function testSaveEngineNotAvailable()
    {
        $this->resource->expects($this->never())->method('save');

        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->search->method('getDoofinderSearchEngines')->with('some-api-key')->willReturn([]);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->model->setValue('sample_hash_id');
        $this->model->save();
    }
}
