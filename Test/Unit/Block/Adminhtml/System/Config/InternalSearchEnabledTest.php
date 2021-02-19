<?php

namespace Doofinder\Feed\Test\Unit\Block\Adminhtml\System\Config;

/**
 * Test class for \Doofinder\Feed\Block\Adminhtml\System\Config\InternalSearchEnabled
 */
class InternalSearchEnabledTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $element;

    /**
     * @var \Magento\Backend\Model\Url
     */
    private $urlBuilder;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    private $context;

    /**
     * @var \Doofinder\Feed\Helper\StoreConfig
     */
    private $storeConfig;

    /**
     * @var \Doofinder\Feed\Block\Adminhtml\System\Config\InternalSearchEnabled
     */
    private $block;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->element = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->setMethods(['setText', 'setComment', 'getHtmlId', 'getElementHtml'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->element->method('getHtmlId')->willReturn('sample_id');
        $this->element->method('getElementHtml')->willReturn('sample value');

        $this->urlBuilder = $this->getMockBuilder(\Magento\Backend\Model\Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->method('getUrlBuilder')->willReturn($this->urlBuilder);

        $this->storeConfig = $this->getMockBuilder(\Doofinder\Feed\Helper\StoreConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $this->objectManager->getObject(
            \Doofinder\Feed\Block\Adminhtml\System\Config\InternalSearchEnabled::class,
            [
                'context' => $this->context,
                'storeConfig' => $this->storeConfig,
            ]
        );
    }

    /**
     * Test render() method if enabled
     *
     * @return void
     */
    public function testRender()
    {
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(true);

        $this->element->expects($this->once())->method('setText')->with('Internal search is enabled.');

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span>' .
                    '</span></label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->block->render($this->element));
    }

    /**
     * Test render() method if disabled
     *
     * @return void
     */
    public function testRenderDisabled()
    {
        $this->storeConfig->method('isInternalSearchEnabled')->willReturn(false);

        $this->urlBuilder->method('getUrl')
            ->with('*/*/*', ['_current' => true, 'section' => 'catalog', '_fragment' => 'catalog_search-link'])
            ->willReturn('http://example.com/link');

        $this->element->expects($this->once())->method('setText')->with('Internal search is disabled.');
        $this->element->expects($this->once())->method('setComment')->with(__(
            'You can enable it %1 by choosing Doofinder in Search Engine field. '
            . 'Enabling internal search requires catalog\'s Update On Save index mode. '
            . 'Index mode will be automatically changed.',
            '<a href="http://example.com/link">here</a>'
        ));

        $expected = '<tr id="row_sample_id"><td class="label"><label for="sample_id"><span>' .
                    '</span></label></td><td class="value">sample value</td><td class=""></td></tr>';
        $this->assertSame($expected, $this->block->render($this->element));
    }
}
