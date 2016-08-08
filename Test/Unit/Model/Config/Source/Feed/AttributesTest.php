<?php

namespace Doofinder\Feed\Test\Unit\Model\Source\Feed;

/**
 * Class AttributesTest
 * @package Doofinder\Feed\Test\Unit\Model\Source\Feed
 */
class AttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigMock;
    /**
     * @var Magento\Eav\Model\Config
     */
    protected $_eavConfigMock;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaperMock;
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $_entityTypeMock;
    /**
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    protected $_model;

    /**
     *
     */
    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_scopeConfigMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $this->_eavConfigMock = $this->getMock(
            '\Magento\Eav\Model\Config',
            [],
            [],
            '',
            false
        );

        $this->_escaperMock = $this->getMock(
            '\Magento\Framework\Escaper',
            null,
            [],
            '',
            false
        );

        $this->_entityTypeMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Type',
            [],
            [],
            '',
            false
        );

        $this->_scopeConfigMock->expects($this->any())
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

        $attrCollectionMock = $this->_objectManager->getCollectionMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection',
            [$eavAttribute]
        );

        $this->_entityTypeMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attrCollectionMock);

        $this->_eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with(\Magento\Catalog\Model\Product::ENTITY)
            ->willReturn($this->_entityTypeMock);

        $this->_model = $this->_objectManager->getObject(
            '\Doofinder\Feed\Model\Config\Source\Feed\Attributes',
            [
                'scopeConfig' => $this->_scopeConfigMock,
                'eavConfig' => $this->_eavConfigMock,
                'escaper' => $this->_escaperMock
            ]
        );
    }

    /**
     * Test toOptionArray() method.
     */
    public function testToOptionArray()
    {
        $expected = array(
            'code' => 'Doofinder: label',
            'attr code' => 'Attribute: attr code'
        );

        $this->assertSame($expected, $this->_model->toOptionArray());
    }

    /**
     * Test getAllAttributes() method.
     */
    public function testGetAllAttributes()
    {
        $expected = array(
            'code' => 'Doofinder: label',
            'attr code' => 'Attribute: attr code'
        );

        $this->assertSame($expected, $this->_model->getAllAttributes());
    }
}
