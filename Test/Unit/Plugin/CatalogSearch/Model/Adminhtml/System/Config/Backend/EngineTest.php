<?php

namespace Doofinder\Feed\Test\Unit\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine
 */
class EngineTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
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
     * @var \Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine
     */
    private $engine;

    /**
     * @var \Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine
     */
    private $plugin;

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

        $this->engine = $this->getMockBuilder(
            \Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine::class
        )->setMethods(['getValue'])
        ->disableOriginalConstructor()
        ->getMock();

        $this->plugin = $this->objectManager->getObject(
            \Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine::class,
            [
                'storeConfig' => $this->storeConfig,
                'searchEngine' => $this->search,
            ]
        );
    }

    /**
     * Test beforeSave()
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $this->engine->method('getValue')->willReturn('doofinder');
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getStoreCodes')->willReturn(['store1', 'store2']);
        $this->storeConfig->method('getManagementServer')->willReturn('eu1-api.doofinder.con');
        $this->storeConfig->method('getSearchServer')->willReturn('eu1-search.doofinder.con');
        $this->storeConfig->method('getHashId')->will($this->returnValueMap([
            ['store1', 'some_hash_1'],
            ['store2', 'some_hash_2'],
        ]));
        $this->search->method('getSearchEngines')->willReturn([
            'some_hash_1' => [],
            'some_hash_2' => [],
        ]);

        $this->plugin->beforeSave($this->engine);
    }

    /**
     * @return void
     */
    public function testBeforeSaveWithoutServers()
    {
        $this->engine->method('getValue')->willReturn('doofinder');
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getStoreCodes')->willReturn(['store1', 'store2']);
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);
        $this->expectExceptionMessage(
            'Please configure Search and/or Management server address before enabling Doofinder search engine.'
        );

        $this->plugin->beforeSave($this->engine);
    }

    /**
     * Test beforeSave() method when search engine is invalid
     *
     * @return void
     */
    public function testBeforeSaveInvalidSearchEngine()
    {
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);

        $this->engine->method('getValue')->willReturn('doofinder');
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getStoreCodes')->willReturn(['store1', 'store3']);
        $this->storeConfig->method('getHashId')->will($this->returnValueMap([
            ['store1', 'some_hash_1'],
            ['store3', 'some_hash_3'],
        ]));
        $this->search->method('getSearchEngines')->willReturn([
            'some_hash_1' => [],
            'some_hash_2' => [],
        ]);
        $this->plugin->beforeSave($this->engine);
    }

    /**
     * Test beforeSave() method when engine is not selected
     *
     * @return void
     */
    public function testBeforeSaveNotSelected()
    {
        $this->engine->method('getValue')->willReturn('mysql');
        $this->plugin->beforeSave($this->engine);
    }

    /**
     * Test beforeSave() method when search engine is invalid
     *
     * @return void
     */
    public function testBeforeSaveNoApiKey()
    {
        $this->engine->method('getValue')->willReturn('doofinder');
        $this->expectException(\Magento\Framework\Exception\ValidatorException::class);
        $this->plugin->beforeSave($this->engine);
    }
}
