<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config;

/**
 * Class InternalSearchEnabledTest
 *
 * @package Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config
 */
class InternalSearchEnabledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $_context;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    protected $_storeConfig;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\System\Config\InternalSearchEnabled
     */
    protected $_block;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_element = $this->getMock(
            '\Magento\Framework\Data\Form\Element\AbstractElement',
            array_merge(
                get_class_methods('\Magento\Framework\Data\Form\Element\AbstractElement'),
                ['setText', 'setComment']
            ),
            [],
            '',
            false
        );
        $this->_element->method('getHtmlId')->willReturn('sample_id');
        $this->_element->method('getElementHtml')->willReturn('sample value');

        $this->_urlBuilder = $this->getMock(
            '\Magento\Backend\Model\Url',
            [],
            [],
            '',
            false
        );

        $this->_context = $this->getMock(
            '\Magento\Backend\Block\Template\Context',
            [],
            [],
            '',
            false
        );
        $this->_context->method('getUrlBuilder')->willReturn($this->_urlBuilder);

        $this->_storeConfig = $this->getMock(
            '\Doofinder\Feed\Helper\StoreConfig',
            [],
            [],
            '',
            false
        );

        $this->_block = $this->_objectManager->getObject(
            '\Doofinder\Feed\Block\Adminhtml\System\Config\InternalSearchEnabled',
            [
                'context' => $this->_context,
                'storeConfig' => $this->_storeConfig,
            ]
        );
    }

    /**
     * Test render() method if enabled.
     */
    public function testRender()
    {
        $this->_storeConfig->method('isInternalSearchEnabled')->willReturn(true);

        $this->_element->expects($this->once())->method('setText')->with('Internal search is enabled.');

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span>' .
                    '</span></label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }

    /**
     * Test render() method if disabled.
     */
    public function testRenderDisabled()
    {
        $this->_storeConfig->method('isInternalSearchEnabled')->willReturn(false);

        $this->_urlBuilder->method('getUrl')
            ->with('*/*/*', ['_current' => true, 'section' => 'catalog', '_fragment' => 'catalog_search-link'])
            ->willReturn('http://example.com/link');

        $this->_element->expects($this->once())->method('setText')->with('Internal search is disabled.');
        $this->_element->expects($this->once())->method('setComment')->with(__(
            'You can enable it %1 by choosing Doofinder in Search Engine field.',
            '<a href="http://example.com/link">here</a>'
        ));

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span>' .
                    '</span></label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->_block->render($this->_element));
    }
}
