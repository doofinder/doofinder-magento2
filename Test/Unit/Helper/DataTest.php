<?php

namespace Doofinder\Feed\Test\Unit\Helper;

/**
 * Test class for \Doofinder\Feed\Helper\Data
 */
class DataTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeConfig;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $module;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Doofinder\Feed\Helper\Data
     */
    private $helper;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeConfig = $this->getMockBuilder(\Magento\Store\Api\Data\StoreConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->module = $this->getMockBuilder(\Magento\Framework\Module\ModuleListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->objectManager->getObject(
            \Doofinder\Feed\Helper\Data::class,
            [
                'storeManager'  => $this->storeManager,
                'moduleList'    => $this->module,
                'logger'        => $this->logger,
            ]
        );
    }

    /**
     * Test getInteger() method
     *
     * @param mixed $param
     * @param boolean $defaultValue
     * @param boolean $expected
     * @return void
     * @dataProvider integerProvider
     */
    public function testGetInteger($param, $defaultValue, $expected)
    {
        $this->assertSame($expected, $this->helper->getInteger($param, $defaultValue));
    }

    /**
     * Data provider for testGetInteger() test
     *
     * @return array
     */
    public function integerProvider()
    {
        return [
            [1, null, 1],
            [1, null, 1],
            ['string', null, null],
            [null, 1, 1]
        ];
    }

    /**
     * Test isBoolean() method
     *
     * @param mixed $value
     * @param boolean $defaultValue
     * @param boolean $expected
     * @return void
     * @dataProvider booleanProvider
     */
    public function testIsBoolean($value, $defaultValue, $expected)
    {
        $this->assertSame($expected, $this->helper->isBoolean($value, $defaultValue));
    }

    /**
     * Data provider for testIsBoolean() test
     *
     * @return array
     */
    public function booleanProvider()
    {
        return [
            [1, false, true],
            ['-2', true, false],
            ['true', false, true],
            ['yes', false, true],
            ['on', false, true],
            ['false', true, false],
            ['no', true, false],
            ['off', true, false],
            ['string', true, true],
            ['string', false, false],
            [null, false, false],
            [-1, true, false],
            [0, true, false],
            [2, false, true],
            [true, false, true],
            [false, true, true]
        ];
    }

    /**
     * Test getBaseUrl() method
     *
     * @return void
     */
    public function testGetBaseUrl()
    {
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeConfig);

        $this->storeConfig->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('localhost'));

        $this->assertSame('localhost', $this->helper->getBaseUrl());
    }

    /**
     * Test getModuleVersion() module
     *
     * @return void
     */
    public function testGetModuleVersion()
    {
        $moduleInfo = [
            'name' => 'Doofinder_Feed',
            'setup_version' => '0.1.0',
            'sequence' => []
        ];

        $this->module->expects($this->once())
            ->method('getOne')
            ->with(\Doofinder\Feed\Helper\Data::MODULE_NAME)
            ->willReturn($moduleInfo);

        $this->assertSame($moduleInfo['setup_version'], $this->helper->getModuleVersion());
    }
}
