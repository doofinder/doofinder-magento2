<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Class HashIdValidationTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class HashIdValidationTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $_storeConfig;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private $_resource;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\HashIdValidation
     */
    private $_model;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_storeConfig = $this->getMock(
            '\Doofinder\Feed\Helper\StoreConfig',
            [],
            [],
            '',
            false
        );
        $this->_storeConfig->method('getStoreCode')->willReturn('current');
        $this->_storeConfig->method('getStoreCodes')->willReturn(['sample1', 'current', 'sample2']);
        $this->_storeConfig->method('getHashId')->will($this->returnValueMap([
            ['current', 'sample_current_hash_id'],
            ['sample1', 'sample_hash_id_1'],
            ['sample2', 'sample_hash_id_2'],
        ]));

        $this->_resource = $this->getMock(
            '\Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [],
            [],
            '',
            false
        );

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\HashIdValidation',
            [
                'storeConfig' => $this->_storeConfig,
                'resource' => $this->_resource,
            ]
        );
    }

    /**
     * Test save()
     *
     * @doesNotPerformAssertions
     */
    public function testSave()
    {
        $this->_model->setValue('sample_hash_id');
        $this->_model->save();
    }

    /**
     * Test save()
     *
     * @doesNotPerformAssertions
     */
    public function testSaveSameAsCurrent()
    {
        $this->_model->setValue('sample_current_hash_id');
        $this->_model->save();
    }

    /**
     * Test save()
     *
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testSaveInvalid()
    {
        $this->_model->setValue('sample_hash_id_2');
        $this->_model->save();
    }
}
