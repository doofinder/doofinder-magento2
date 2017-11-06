<?php

namespace Doofinder\Feed\Test\Unit;

use Magento\Framework\TestFramework\Unit\BaseTestCase as FrameworkBaseTestCase;
use PHPUnit_Framework_MockObject_Generator;

class BaseTestCase extends FrameworkBaseTestCase
{
    /**
     * @var array
     */
    private $myMockObjectGenerator;

    /**
     * Returns a mock object for the specified class.
     *
     * Fallback for PHPUnit 4 present in Magento 2.1.x
     *
     * @depracated
     * @see PHPUnit_Framework_TestCase
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getMock(
        $originalClassName,
        $methods = [],
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $cloneArguments = false,
        $callOriginalMethods = false
    ) {
        if (is_callable('parent::getMock')) {
            return parent::getMock(
                $originalClassName,
                $methods,
                $arguments,
                $mockClassName,
                $callOriginalConstructor,
                $callOriginalClone,
                $callAutoload,
                $cloneArguments,
                $callOriginalMethods
            );
        }

        $mockObject = $this->getMyMockObjectGenerator()->getMock(
            $originalClassName,
            $methods,
            $arguments,
            $mockClassName,
            $callOriginalConstructor,
            $callOriginalClone,
            $callAutoload,
            $cloneArguments,
            $callOriginalMethods
        );

        $this->registerMockObject($mockObject);

        return $mockObject;
    }

    /**
     * Get the mock object generator, creating it if it doesn't exist.
     *
     * @return   PHPUnit_Framework_MockObject_Generator
     */
    private function getMyMockObjectGenerator()
    {
        if (null === $this->myMockObjectGenerator) {
            $this->myMockObjectGenerator = new PHPUnit_Framework_MockObject_Generator;
        }

        return $this->myMockObjectGenerator;
    }
}
