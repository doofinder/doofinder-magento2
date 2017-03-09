<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\Password
 */
class PasswordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\Password
     */
    protected $_model;

    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_model = $this->_objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Backend\Password'
        );
    }

    /**
     * Test beforeSave()
     *
     * @dataProvider testBeforeSaveDataProvider
     */
    public function testBeforeSave($value)
    {
        $this->_model->setValue($value);
        $this->_model->beforeSave();
    }

    public function testBeforeSaveDataProvider()
    {
        return [
            [''],
            ['abc'],
            ['-_'],
            ['123'],
            ['-abc_xyz-ABC-XYZ_012-789_'],
        ];
    }

    /**
     * Test beforeSave()
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Sample field value is invalid.
     *                           Only alphanumeric characters with underscores (_) and hyphens (-) are allowed.
     * @dataProvider testBeforeSaveInvalidDataProvider
     */
    public function testBeforeSaveInvalid($value)
    {
        $config = $this->_model->getFieldConfig();
        $config['label'] = 'Sample field';
        $this->_model->setFieldConfig($config);
        $this->_model->setValue($value);
        $this->_model->beforeSave();
    }

    public function testBeforeSaveInvalidDataProvider()
    {
        return [
            ['abc$'],
            ['-_.'],
            ['12 3'],
            [','],
            ['&^#**&$%'],
            ['`'],
            ['/'],
        ];
    }
}
