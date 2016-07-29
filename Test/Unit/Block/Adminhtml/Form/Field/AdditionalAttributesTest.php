<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\Form\Field;

/**
 * Class AdditionalAttributesTest
 *
 * @package Doofinder\Feed\Test\Unit\Block\Adminhtml\Form\Field
 */
class AdditionalAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\View\Element\Context
     */
    protected $_contextMock;
    /**
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    protected $_feedAttributesMock;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaperMock;
    /**
     * @var \Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes
     */
    protected $_block;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Context')
            ->setMethods(['getEscaper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_feedAttributesMock = $this->getMock(
            '\Doofinder\Feed\Model\Config\Source\Feed\Attributes',
            [],
            [],
            '',
            false
        );

        $this->_escaperMock = $this->getMock('\Magento\Framework\Escaper',
            ['escapeHtml'],
            [],
            '',
            false
        );

        $this->_contextMock->expects($this->once())
            ->method('getEscaper')
            ->willReturn($this->_escaperMock);

        $this->_feedAttributesMock->expects($this->once())
            ->method('getAllAttributes')
            ->willReturn(['code' => 'label', 'code2' => 'label2']);

        $this->_block = $this->_objectManager->getObject(
            '\Doofinder\Feed\Block\Adminhtml\Form\Field\AdditionalAttributes',
            [
                'context' => $this->_contextMock,
                'feedAttributes' => $this->_feedAttributesMock
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
