<?php

namespace Doofinder\Feed\Test\Integration\Model\Generator\Component\Processor;

use Magento\TestFramework\TestCase\AbstractIntegrity;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Processor\Xml
 */
class XmlTest extends AbstractIntegrity
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
    private $xml;

    /**
     * @var \Doofinder\Feed\Model\Generator\Item[]
     */
    private $items;

    /**
     * @var string|null
     */
    private $currentFile;

    /**
     * Set up test
     *
     * @return void
     */
    public function setUp()
    {
        $this->items = [];

        $this->items[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Doofinder\Feed\Model\Generator\Item::class,
            [
                'data' => [
                    'title' => 'Sample title',
                ]
            ]
        );

        $this->items[] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Doofinder\Feed\Model\Generator\Item::class,
            [
                'data' => [
                    'title' => 'Sample title 2',
                ]
            ]
        );

        $this->xml = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Doofinder\Feed\Model\Generator\Component\Processor\Xml::class
        );

        /**
         * Make sure tmp directory exists
         * @notice For some reason without it tests fails
         */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\App\Filesystem::class
        );
        $dir = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP);
        $dir->create();
    }

    /**
     * Tear down test
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->currentFile) {
            // @codingStandardsIgnoreStart
            unlink($this->currentFile);
            // @codingStandardsIgnoreEnd
            $this->currentFile = null;
        }
    }

    /**
     * Test whole feed
     *
     * @return void
     */
    public function testWholeFeed()
    {
        $this->xml->process($this->items);

        $expected = $this::HEAD . $this::ITEM_1 . $this::ITEM_2 . $this::FOOT;

        $this->assertStringMatchesFormat($expected, $this->xml->getFeed());
    }

    /**
     * Test feed body
     *
     * @return void
     */
    public function testFeedBody()
    {
        $this->xml->setStart(false);
        $this->xml->setEnd(false);
        $this->xml->process($this->items);

        $expected = $this::ITEM_1 . $this::ITEM_2;

        $this->assertStringMatchesFormat($expected, $this->xml->getFeed());
    }

    /**
     * Test feed open
     *
     * @return void
     */
    public function testFeedOpen()
    {
        $this->xml->setEnd(false);
        $this->xml->process([]);

        $expected = $this::HEAD;

        $this->assertStringMatchesFormat($expected, $this->xml->getFeed());
    }

    /**
     * Test feed close
     *
     * @return void
     */
    public function testFeedClose()
    {
        $this->xml->setStart(false);
        $this->xml->process([]);

        $expected = $this::FOOT;

        $this->assertStringMatchesFormat($expected, $this->xml->getFeed());
    }

    /**
     * Test whole feed file
     *
     * @return void
     */
    public function testWholeFeedFile()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\App\Filesystem::class
        );
        $dir = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        $this->currentFile = $dir->getAbsolutePath('test.xml');
        $this->xml->setDestinationFile($this->currentFile);

        $this->xml->process($this->items);

        $expected = $this::HEAD . $this::ITEM_1 . $this::ITEM_2 . $this::FOOT;

        $this->assertStringMatchesFormat($expected, $dir->readFile('test.xml'));
    }

    /**
     * Test feed file
     *
     * @return void
     */
    public function testFeedFile()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\App\Filesystem::class
        );
        $dir = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::TMP);

        $this->currentFile = $dir->getAbsolutePath('test.xml');
        $this->xml->setDestinationFile($this->currentFile);

        $this->xml->setEnd(false);
        $this->xml->process([$this->items[0]]);

        $expected = $this::HEAD . $this::ITEM_1;

        $this->assertStringMatchesFormat($expected, $dir->readFile('test.xml'));

        $this->xml->setStart(false);
        $this->xml->setEnd(true);
        $this->xml->process([$this->items[1]]);

        $expected = $this::HEAD . $this::ITEM_1 . $this::ITEM_2 . $this::FOOT;

        $this->assertStringMatchesFormat($expected, $dir->readFile('test.xml'));
    }
}
