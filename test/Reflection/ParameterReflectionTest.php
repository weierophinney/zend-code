<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use Zend\Code\Reflection;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_Parameter
 */
class ParameterReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'], 0);
        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $parameter->getDeclaringClass());
    }

    public function testClassReturn_NoClassGiven_ReturnsNull()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'], 'param1');
        $this->assertNull($parameter->getClass());
    }

    public function testClassReturn()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass2', 'getProp2'], 'param2');
        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $parameter->getClass());
    }

    /**
     * @dataProvider paramTypeTestProvider
     */
    public function testTypeReturn($param, $type)
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\TestSampleClass5', 'doSomething'], $param);
        $this->assertEquals($type, $parameter->getType());
    }

    public function testCallableTypeHint()
    {
        $parameter = new Reflection\ParameterReflection(['ZendTest\Code\Reflection\TestAsset\CallableTypeHintClass', 'foo'], 'bar');
        $this->assertEquals('callable', $parameter->getType());
    }

    public function paramTypeTestProvider()
    {
        return [
            ['one','int'],
            ['two','int'],
            ['three','string'],
            ['array','array'],
            ['class','ZendTest\Code\Reflection\TestAsset\TestSampleClass']
        ];
    }
}
