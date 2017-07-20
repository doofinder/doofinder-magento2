<?php

namespace Doofinder\Feed\Test\Unit\Model\Source\Feed;

use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Class AttributesTest
 * @package Doofinder\Feed\Test\Unit\Model\Source\Feed
 */
class AttributesTest extends BaseTestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $_escaper;

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    private $_entityType;

    /**
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    private $_model;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->_scopeConfig = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_eavConfig = $this->getMock(
            '\Magento\Eav\Model\Config',
            [],
            [],
            '',
            false
        );

        $this->_escaper = $this->getMock(
            '\Magento\Framework\Escaper',
            null,
            [],
            '',
            false
        );

        $this->_entityType = $this->getMock(
            '\Magento\Eav\Model\Entity\Type',
            [],
            [],
            '',
            false
        );

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(['code' => 'label']));

        $eavAttribute = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeLabel', 'getAttributeCode'])
            ->getMock();

        $eavAttribute->expects($this->any())
            ->method('getAttributeLabel')
            ->willReturn('attr label');

        $eavAttribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('attr code');

        $attrCollection = $this->objectManager->getCollectionMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection',
            [$eavAttribute]
        );

        $this->_entityType->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attrCollection);

        $this->_eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->willReturn($this->_entityType);

        $this->_model = $this->objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Source\Feed\Attributes',
            [
                'scopeConfig' => $this->_scopeConfig,
                'eavConfig' => $this->_eavConfig,
                'escaper' => $this->_escaper
            ]
        );
    }

    /**
     * Test toOptionArray() method.
     */
    public function testToOptionArray()
    {
        $expected = [
            'code' => 'Doofinder: label',
            'attr code' => 'Attribute: attr code'
        ];

        $this->assertSame($expected, $this->_model->toOptionArray());
    }

    /**
     * Test getAllAttributes() method.
     */
    public function testGetAllAttributes()
    {
        $expected = [
            'code' => 'Doofinder: label',
            'attr code' => 'Attribute: attr code'
        ];

        $this->assertSame($expected, $this->_model->getAllAttributes());
    }
}
