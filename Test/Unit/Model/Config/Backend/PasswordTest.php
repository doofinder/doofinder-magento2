<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

use Doofinder\Feed\Test\Unit\BaseTestCase;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\Password
 */
class PasswordTest extends BaseTestCase
{
    /**
     * @var \Doofinder\Feed\Model\Config\Backend\Password
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

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\Password::class
        );
    }

    /**
     * Test beforeSave() method
     *
     * @param  string $value
     * @return void
     * @dataProvider providerTestBeforeSaveData
     * @doesNotPerformAssertions
     */
    public function testBeforeSave($value)
    {
        $this->model->setValue($value);
        $this->model->beforeSave();
    }

    /**
     * Data provider for testBeforeSave() test
     *
     * @return array
     */
    public function providerTestBeforeSaveData()
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
     * Test beforeSave() method
     *
     * @param  string $value
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Sample field value is invalid.
     *                           Only alphanumeric characters with underscores (_) and hyphens (-) are allowed.
     * @dataProvider providerTestBeforeSaveInvalidData
     */
    public function testBeforeSaveInvalid($value)
    {
        $config = $this->model->getFieldConfig();
        $config['label'] = 'Sample field';
        $this->model->setFieldConfig($config);
        $this->model->setValue($value);
        $this->model->beforeSave();
    }

    /**
     * Data provider for testBeforeSaveInvalid() test
     *
     * @return array
     */
    public function providerTestBeforeSaveInvalidData()
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
