<?php

namespace Doofinder\Feed\Test\Unit;

use Magento\Framework\TestFramework\Unit\BaseTestCase as FrameworkBaseTestCase;

/**
 * Base test class
 * General class for tests in project
 * @SuppressWarnings(PHPMD.NumberOfChildren) @codingStandardsIgnoreLine
 */
class BaseTestCase extends FrameworkBaseTestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_Generator|\PHPUnit\Framework\MockObject\Generator
     */
    private $myMockObjectGenerator;

    /**
     * Returns a mock object for the specified class.
     *
     * Fallback for PHPUnit 4 present in Magento 2.1.x
     *
     * @param  string $originalClassName
     * @param  array|null $methods
     * @param  array $arguments
     * @param  string $mockClassName
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @param  boolean $cloneArguments
     * @param  boolean $callOriginalMethods
     * @return PHPUnit_Framework_MockObject
     * @see PHPUnit_Framework_TestCase
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.NumberOfChildren)
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
     * @return PHPUnit_Framework_MockObject_Generator
     * @throws \Exception Cannot find MockObject Generator.
     */
    private function getMyMockObjectGenerator()
    {
        if (null === $this->myMockObjectGenerator) {
            if (class_exists(\PHPUnit_Framework_MockObject_Generator::class)) {
                $this->myMockObjectGenerator = new \PHPUnit_Framework_MockObject_Generator;
            } elseif (class_exists(\PHPUnit\Framework\MockObject\Generator::class)) {
                $this->myMockObjectGenerator = new \PHPUnit\Framework\MockObject\Generator;
            }
        }
        if (null === $this->myMockObjectGenerator) {
            throw new \Exception('Cannot initialize MockObject Generator'); // @codingStandardsIgnoreLine
        }
        return $this->myMockObjectGenerator;
    }
}
