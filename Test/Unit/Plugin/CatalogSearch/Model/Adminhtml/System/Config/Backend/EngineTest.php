<?php

namespace Doofinder\Feed\Test\Unit\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine
 */
class EngineTest extends BaseTestCase
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
    public function setUp()
    {
        parent::setUp();

        $this->storeConfig = $this->getMock(
            \Doofinder\Feed\Helper\StoreConfig::class,
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

        $this->engine = $this->getMock(
            \Magento\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine::class,
            ['getValue'],
            [],
            '',
            false
        );

        $this->plugin = $this->objectManager->getObject(
            \Doofinder\Feed\Plugin\CatalogSearch\Model\Adminhtml\System\Config\Backend\Engine::class,
            [
                'storeConfig' => $this->storeConfig,
                'search' => $this->search,
            ]
        );
    }

    /**
     * Test beforeSave()
     *
     * @return void
     * @doesNotPerformAssertions
     */
    public function testBeforeSave()
    {
        $this->engine->method('getValue')->willReturn('doofinder');
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getStoreCodes')->willReturn(['store1', 'store2']);
        $this->storeConfig->method('getHashId')->will($this->returnValueMap([
            ['store1', 'some_hash_1'],
            ['store2', 'some_hash_2'],
        ]));
        $this->search->method('getDoofinderSearchEngines')->with('some-api-key')->willReturn([
            'some_hash_1' => [],
            'some_hash_2' => [],
        ]);

        $this->plugin->beforeSave($this->engine);
    }

    /**
     * Test beforeSave() method when search engine is invalid
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testBeforeSaveInvalidSearchEngine()
    {
        $this->engine->method('getValue')->willReturn('doofinder');
        $this->storeConfig->method('getApiKey')->willReturn('some-api-key');
        $this->storeConfig->method('getStoreCodes')->willReturn(['store1', 'store3']);
        $this->storeConfig->method('getHashId')->will($this->returnValueMap([
            ['store1', 'some_hash_1'],
            ['store3', 'some_hash_3'],
        ]));
        $this->search->method('getDoofinderSearchEngines')->with('some-api-key')->willReturn([
            'some_hash_1' => [],
            'some_hash_2' => [],
        ]);
        $this->plugin->beforeSave($this->engine);
    }

    /**
     * Test beforeSave() method when engine is not selected
     *
     * @return void
     * @doesNotPerformAssertions
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
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testBeforeSaveNoApiKey()
    {
        $this->engine->method('getValue')->willReturn('doofinder');
        $this->plugin->beforeSave($this->engine);
    }
}
