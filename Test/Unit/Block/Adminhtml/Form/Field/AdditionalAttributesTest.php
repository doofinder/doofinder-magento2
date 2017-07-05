<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\Form\Field;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class AdditionalAttributesTest
 *
 * @package Doofinder\Feed\Test\Unit\Block\Adminhtml\Form\Field
 */
class AdditionalAttributesTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\View\Element\Context
     */
    private $_context;

    /**
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    private $_feedAttributes;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $_escaper;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    private $_block;

    /**
     * Prepares the environment before running a test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_context = $this->getMockBuilder('\Magento\Framework\View\Element\Context')
            ->setMethods(['getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_feedAttributes = $this->getMock(
            '\Doofinder\Feed\Model\Config\Source\Feed\Attributes',
            [],
            [],
            '',
            false
        );

        $this->_escaper = $this->getMock(
            '\Magento\Framework\Escaper',
            ['escapeHtml'],
            [],
            '',
            false
        );

        $this->_context->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->_escaper);

        $this->_feedAttributes->expects($this->once())
            ->method('getAllAttributes')
            ->willReturn(['code' => 'label', 'code2' => 'label2']);

        $this->_block = $this->objectManager->getObject(
            '\Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes',
            [
                'context' => $this->_context,
                'feedAttributes' => $this->_feedAttributes
            ]
        );
    }

    /**
     * Test _toHtml() method.
     */
    public function testToHtml()
    {
        $expected = '<select name="" id="" class="" title="" >';
        $expected .= '<option value="" ></option><option value="" ></option>';
        $expected .= '</select>';

        $this->assertSame($expected, $this->_block->_toHtml());
    }
}
