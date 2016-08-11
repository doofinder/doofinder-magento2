<?php

namespace Doofinder\Feed\Test\Integration\Model\Generator\Component\Fetcher;

/**
 * Test class for \Doofinder\Feed\Model\Generator\Component\Fetcher\Processor
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testFetchMultiwebsite()
    {
        /** @var \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositoryFactory */
        $websiteRepositoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Store\Api\WebsiteRepositoryInterface'
        );

        $website = $websiteRepositoryFactory->get('test');

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
        $productRepositoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Catalog\Api\ProductRepositoryInterface'
        );

        $product = $productRepositoryFactory->get('simple1');
        $product->setWebsiteIds([$website->getId()])
            ->save();

        $fetcher = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Doofinder\Feed\Model\Generator\Component\Fetcher\Product'
        );

        $this->assertEquals(
            [
                'simple2',
                'simple3',
            ],
            array_map(function ($item) {
                return $item->getContext()->getSku();
            }, $fetcher->fetch())
        );
    }
}
