<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\Form\Field;

/**
 * Test class for \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
 */
class AdditionalAttributesTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Magento\Framework\View\Element\Context
     */
    private $context;

    /**
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    private $feedAttributes;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    private $block;

    /**
     * Test run() method
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->feedAttributes = $this->getMockBuilder(\Doofinder\Feed\Model\Config\Source\Feed\Attributes::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManager);
        $this->context->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
        $this->context->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->escaper);

        $this->feedAttributes->expects($this->once())
            ->method('toOptionArray')
            ->willReturn(['code' => 'label', 'code2' => 'label2']);

        $this->block = $this->objectManager->getObject(
            \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes::class,
            [
                'context' => $this->context,
                'feedAttributes' => $this->feedAttributes
            ]
        );
    }

    /**
     * Test _toHtml() method
     *
     * @return void
     */
    public function testToHtml()
    {
        $expected = '<select name="" id="" class="" title="" >';
        $expected .= '<option value="" ></option><option value="" ></option>';
        $expected .= '</select>';

        $this->assertSame($expected, $this->block->toHtml());
    }
}
