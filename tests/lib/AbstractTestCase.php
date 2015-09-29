<?php

namespace Improv\DateTime\Test;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Creates a complete mock, leaving within it no implementation code.
     *
     * @param string $class_name The name of the class to mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFullMock($class_name)
    {
        return $this->getMockBuilder($class_name)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock object, intended to leave behind certain implementation
     *  code. This is to be used only in the most tricky of situations...
     *
     * @param string $class_name The name of the class to mock
     * @param array  $methods    Methods to mock
     * @param array  $args       Constructor arguments for the mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartialMock($class_name, array $methods, array $args = [])
    {
        $builder = $this->getMockBuilder($class_name)
            ->setMethods($methods);

        if ($args) {
            $builder->setConstructorArgs($args);
        }

        return $builder->getMock();
    }
}
