<?php

namespace Doofinder\Feed\Test\Unit\Model\Backend;

/**
 * Class HashIdValidationTest
 * @package Doofinder\Feed\Test\Unit\Model\Backend
 */
class HashIdValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $_resource;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\HashIdValidation
     */
    protected $_model;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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

        $this->_model = $this->_objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\HashIdValidation',
            [
                'storeConfig' => $this->_storeConfig,
                'resource' => $this->_resource,
            ]
        );
    }

    /**
     * Test save()
     */
    public function testSave()
    {
        $this->_model->setValue('sample_hash_id');
        $this->_model->save();
    }

    /**
     * Test save()
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
