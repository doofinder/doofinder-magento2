<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\Password
 */
class PasswordTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Config\Backend\Password
     */
    private $_model;

    public function setUp()
    {
        parent::setUp();

        $this->_model = $this->objectManager->getObject(
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
