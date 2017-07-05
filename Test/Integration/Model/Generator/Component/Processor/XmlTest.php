<?php

namespace Doofinder\Feed\Test\Integration\Model\Generator\Component\Processor;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Xml
 */
class XmlTest extends \PHPUnit_Framework_TestCase
{
    const HEAD = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
<title><![CDATA[Product feed]]></title>
<link><![CDATA[%s/doofinder/feed]]></link>
<pubDate><![CDATA[%s, %d %s %d %d:%d:%d UTC]]></pubDate>
<generator><![CDATA[Doofinder/%d.%d.%d]]></generator>
<description><![CDATA[Magento Product feed for Doofinder]]></description>

EOT;

    const ITEM_1 = <<<EOT
<item>
 <title><![CDATA[Sample title]]></title>
</item>

EOT;

    const ITEM_2 = <<<EOT
<item>
 <title><![CDATA[Sample title 2]]></title>
</item>

EOT;

    const FOOT = <<<EOT
</channel>
</rss>

EOT;

    /**
     * @var \Doofinder\Feed\Model\Generator\Component\Processor\Xml
     */
    private $_xml;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    private $_items;

    /**
     * @var string|null
     */
    private $_currentFile;

    public function setUp()
    {
        $this->_items = [];

        $this->_items[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Doofinder\Feed\Model\Generator\Item',
            [
                'data' => [
                    'title' => 'Sample title',
                ]
            ]
        );

        $this->_items[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Doofinder\Feed\Model\Generator\Item',
            [
                'data' => [
                    'title' => 'Sample title 2',
                ]
            ]
        );

        $this->_xml = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Doofinder\Feed\Model\Generator\Component\Processor\Xml'
        );

        /**
         * Make sure tmp directory exists
         * @notice For some reason without it tests fails
         */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\TestFramework\App\Filesystem'
        );
        $dir = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $dir->create();
    }

    public function tearDown()
    {
        if ($this->_currentFile) {
            // @codingStandardsIgnoreStart
            unlink($this->_currentFile);
            // @codingStandardsIgnoreEnd
            $this->_currentFile = null;
        }
    }

    public function testWholeFeed()
    {
        $this->_xml->process($this->_items);

        $expected = $this::HEAD . $this::ITEM_1 . $this::ITEM_2 . $this::FOOT;

        $this->assertStringMatchesFormat($expected, $this->_xml->getFeed());
    }

    public function testFeedBody()
    {
        $this->_xml->setStart(false);
        $this->_xml->setEnd(false);
        $this->_xml->process($this->_items);

        $expected = $this::ITEM_1 . $this::ITEM_2;

        $this->assertStringMatchesFormat($expected, $this->_xml->getFeed());
    }

    public function testFeedOpen()
    {
        $this->_xml->setEnd(false);
        $this->_xml->process([]);

        $expected = $this::HEAD;

        $this->assertStringMatchesFormat($expected, $this->_xml->getFeed());
    }

    public function testFeedClose()
    {
        $this->_xml->setStart(false);
        $this->_xml->process([]);

        $expected = $this::FOOT;

        $this->assertStringMatchesFormat($expected, $this->_xml->getFeed());
    }

    public function testWholeFeedFile()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\TestFramework\App\Filesystem'
        );
        $dir = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        $this->_currentFile = $dir->getAbsolutePath('test.xml');
        $this->_xml->setDestinationFile($this->_currentFile);

        $this->_xml->process($this->_items);

        $expected = $this::HEAD . $this::ITEM_1 . $this::ITEM_2 . $this::FOOT;

        $this->assertStringMatchesFormat($expected, $dir->readFile('test.xml'));
    }

    public function testFeedFile()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\TestFramework\App\Filesystem'
        );
        $dir = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        $this->_currentFile = $dir->getAbsolutePath('test.xml');
        $this->_xml->setDestinationFile($this->_currentFile);

        $this->_xml->setEnd(false);
        $this->_xml->process([$this->_items[0]]);

        $expected = $this::HEAD . $this::ITEM_1;

        $this->assertStringMatchesFormat($expected, $dir->readFile('test.xml'));

        $this->_xml->setStart(false);
        $this->_xml->setEnd(true);
        $this->_xml->process([$this->_items[1]]);

        $expected = $this::HEAD . $this::ITEM_1 . $this::ITEM_2 . $this::FOOT;

        $this->assertStringMatchesFormat($expected, $dir->readFile('test.xml'));
    }
}
