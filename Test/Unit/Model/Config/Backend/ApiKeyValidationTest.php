<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\ApiKeyValidation
 */
class ApiKeyValidationTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
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
            \Doofinder\Feed\Model\Config\Backend\ApiKeyValidation::class,
            [
                'storeConfig' => $this->storeConfig,
                'searchEngine' => $this->search,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * Test save() method with empty value
     *
     * @return void
     */
    public function testSaveEmpty()
    {
        $this->resource->expects($this->never())->method('save');
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(false);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);
        $this->model->setValue(null);
        $this->model->save();
    }

    /**
     * Test save() method with empty value with engine enabled
     *
     * @return void
     */
    public function testSaveEmptyEngineEnabled()
    {
        $this->resource->expects($this->never())->method('save');
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(true);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->model->setValue(null);
        $this->model->save();
    }

    /**
     * Test save() method valid format validation
     *
     * @return void
     */
    public function testSaveValidFormat()
    {
        $this->resource->expects($this->once())->method('save');
        $this->model->setValue('eu1-abcdef0123456789abcdef0123456789abcdef01');
        $this->model->save();
    }

    /**
     * Test save() method with invalid api key
     *
     * @return void
     */
    public function testSaveInvalidApiKey()
    {
        $apiKey = 'eu1-0000000000000000000000000000000000000000';
        $this->resource->expects($this->never())->method('save');
        $this->search->method('getSearchEngines')->with($apiKey)->will(
            $this->throwException(new \Doofinder\Management\Errors\NotAllowed('Error', 0, null, 'Error'))
        );
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);
        $this->model->setValue($apiKey);
        $this->model->save();
    }

    
}
