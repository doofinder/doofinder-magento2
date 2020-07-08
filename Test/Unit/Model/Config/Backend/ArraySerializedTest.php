<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Backend;

/**
 * Test class for \Doofinder\Feed\Model\Config\Backend\ArraySerialized
 */
class ArraySerializedTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configScope;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $managerInterface;

    /**
     * @var \Doofinder\Feed\Model\Config\Backend\ArraySerialized
     */
    private $model;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configScope = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheTypeList = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerInterface = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->managerInterface);

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Backend\ArraySerialized::class,
            [
                'context' => $this->context,
                'registry' => $this->registry,
                'config' => $this->configScope,
                'cacheTypeList' => $this->cacheTypeList,
            ]
        );
    }

    /**
     * Test afterSave() method
     *
     * @return void
     */
    public function testAfterSave()
    {
        $this->model->afterSave();
    }
}
