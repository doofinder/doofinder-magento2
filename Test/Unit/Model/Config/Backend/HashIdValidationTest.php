<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\HashIdValidation
 */
class HashIdValidationTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

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
        $this->storeConfig->method('getStoreCode')->willReturn('current');
        $this->storeConfig->method('getStoreCodes')->willReturn(['sample1', 'current', 'sample2']);
        $this->storeConfig->method('getHashId')->will($this->returnValueMap([
            ['current', 'sample_current_hash_id'],
            ['sample1', 'sample_hash_id_1'],
            ['sample2', 'sample_hash_id_2'],
        ]));

        $this->resource = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\HashIdValidation::class,
            [
                'storeConfig' => $this->storeConfig,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * Test save() method
     *
     * @return void
     * @doesNotPerformAssertions
     */
    public function testSave()
    {
        $this->model->setValue('sample_hash_id');
        $this->model->save();
    }

    /**
     * Test save() method
     *
     * @return void
     * @doesNotPerformAssertions
     */
    public function testSaveSameAsCurrent()
    {
        $this->model->setValue('sample_current_hash_id');
        $this->model->save();
    }

    /**
     * Test save() method
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testSaveInvalid()
    {
        $this->model->setValue('sample_hash_id_2');
        $this->model->save();
    }
}
