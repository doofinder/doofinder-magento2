<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\HashIdValidation
 */
class IndicesUpdateModeValidationTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Model\Api\SearchEngine
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

        $this->search = $this->getMockBuilder(\Doofinder\Feed\Model\Api\SearchEngine::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\IndicesUpdateModeValidation::class,
            [
                'storeConfig' => $this->storeConfig,
                'searchEngine' => $this->search,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * Test save() method with indices update mode set to Feed
     *
     * @return void
     */
    public function testSaveModeFeed()
    {
        $this->model->setValue('feed');
        $this->model->save();
    }

    /**
     * Test save() method with indices update mode set to Doofinder Api
     *
     * @return void
     */
    public function testSaveModeApi()
    {
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getHashId')->willReturn('sample_hash_id');

        $this->model->setValue('api');
        $this->model->save();
    }

    /**
     * Test save() method setting empty hash id and indices update mode to Doofinder Api (value 'api')
     *
     * @return void
     */
    public function testSaveModeApiEmptyApiKey()
    {
        $this->storeConfig->method('getApiKey')->willReturn(null);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->model->setValue('api');
        $this->model->save();
    }

    /**
     * Test save() method setting empty hash id and indices update mode to Doofinder Api (value 'api')
     *
     * @return void
     */
    public function testSaveModeApiEmptyHashId()
    {
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getHashId')->willReturn(null);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->model->setData('fieldset_data', ['hash_id' => '']);
        $this->model->setValue('api');
        $this->model->save();
    }
}
