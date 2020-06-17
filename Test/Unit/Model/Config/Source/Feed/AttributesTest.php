<?php

namespace Doofinder\Feed\Test\Unit\Model\Config\Source\Feed;

/**
 * Test class for \Doofinder\Feed\Model\Config\Source\Feed\Attributes
 */
class AttributesTest extends \Doofinder\FeedCompatibility\Test\Unit\Base
{
    /**
     * @var \Doofinder\Feed\Model\Config\Source\Feed\Attributes
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $doofinderProvider;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setupTests()
    {
        $this->catalogProvider = $this->getMockBuilder(\Doofinder\Feed\Model\Attributes\Catalog::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->doofinderProvider = $this->getMockBuilder(\Doofinder\Feed\Model\Attributes\Doofinder::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Doofinder\Feed\Model\Config\Source\Feed\Attributes::class,
            [
                'providers' => [
                    $this->catalogProvider,
                    $this->doofinderProvider,
                ]
            ]
        );
    }

    /**
     * Test toOptionArray() method
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $expected = [
            'attr_code' => 'attr label',
            'doo_attr_code' => 'attr_label'
        ];
        $this->catalogProvider->expects($this->once())->method('toOptionArray')->willReturn([
            'attr_code' => 'attr label',
        ]);
        $this->doofinderProvider->expects($this->once())->method('toOptionArray')->willReturn([
            'doo_attr_code' => 'attr_label'
        ]);

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
