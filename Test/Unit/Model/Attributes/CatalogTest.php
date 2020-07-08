<?php

namespace Doofinder\Feed\Test\Unit\Model\Attributes;

/**
 * Test class for \Doofinder\Feed\Model\Attributes\Catalog
 */
class CatalogTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Model\Attributes\Catalog
     */
    private $testedClass;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $eavConfig;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $eavCollection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $type;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavCollection = $this->getMockBuilder(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class
        )->disableOriginalConstructor()->getMock();

        $this->type = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testedClass = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Attributes\Catalog::class,
            [
                'eavConfig' => $this->eavConfig
            ]
        );
    }

    /**
     * @return void
     */
    public function testToOptionArray()
    {
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getFrontendLabel'])
            ->getMock();
        $attribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('attr_code');
        $attribute->expects($this->once())
            ->method('getFrontendLabel')
            ->willReturn('Attr label');

        $this->type = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->type->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($this->eavCollection);

        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->willReturn($this->type);

        $this->eavCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$attribute]));

        $this->assertSame(
            'Attribute: attr_code (Attr label)',
            $this->testedClass->toOptionArray()['attr_code']->__toString()
        );
    }
}
