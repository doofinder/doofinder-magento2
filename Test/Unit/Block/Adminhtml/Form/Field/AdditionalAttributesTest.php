<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\Form\Field;

/**
 * Test class for \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
 */
class AdditionalAttributesTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
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
    public function setUp()
    {
        parent::setUp();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->setMethods(['getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->feedAttributes = $this->getMockBuilder(\Doofinder\Feed\Model\Config\Source\Feed\Attributes::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->setMethods(['escapeHtml'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->escaper);

        $this->feedAttributes->expects($this->once())
            ->method('getAllAttributes')
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

        $this->assertSame($expected, $this->block->_toHtml());
    }
}
