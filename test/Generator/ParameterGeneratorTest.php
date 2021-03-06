<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator;

use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\ValueGenerator;
use Zend\Code\Reflection\ParameterReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class ParameterGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testTypeGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setType('Foo');
        $this->assertEquals('Foo', $parameterGenerator->getType());
    }

    public function testNameGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setName('Foo');
        $this->assertEquals('Foo', $parameterGenerator->getName());
    }

    public function testDefaultValueGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();

        $value = new ValueGenerator('Foo', ValueGenerator::TYPE_CONSTANT);
        $parameterGenerator->setDefaultValue($value);
        $this->assertEquals('Foo', (string) $parameterGenerator->getDefaultValue());
    }

    public function testPositionGetterAndSetterPersistValue()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setPosition(2);
        $this->assertEquals(2, $parameterGenerator->getPosition());
    }

    public function testGenerateIsCorrect()
    {
        $parameterGenerator = new ParameterGenerator();
        $parameterGenerator->setType('Foo');
        $parameterGenerator->setName('bar');
        $parameterGenerator->setDefaultValue(15);
        $this->assertEquals('Foo $bar = 15', $parameterGenerator->generate());

        $parameterGenerator->setDefaultValue('foo');
        $this->assertEquals('Foo $bar = \'foo\'', $parameterGenerator->generate());
    }

    public function testFromReflectionGetParameterName()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('name');
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $this->assertEquals('param', $codeGenParam->getName());
    }

    public function testFromReflectionGetParameterType()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('type');
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $this->assertEquals('\\stdClass', $codeGenParam->getType());
    }

    public function testFromReflectionGetReference()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('reference');
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $this->assertTrue($codeGenParam->getPassedByReference());
    }

    public function testFromReflectionGetDefaultValue()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('defaultValue');
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $defaultValue = $codeGenParam->getDefaultValue();
        $this->assertEquals('\'foo\'', (string) $defaultValue);
    }

    public function testFromReflectionGetArrayHint()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('fromArray');
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $this->assertEquals('array', $codeGenParam->getType());
    }

    public function testFromReflectionGetWithNativeType()
    {
        $reflectionParameter = $this->getFirstReflectionParameter('hasNativeDocTypes');
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $this->assertNotEquals('int', $codeGenParam->getType());
        $this->assertEquals('', $codeGenParam->getType());
    }

    public function testCallableTypeHint()
    {
        $parameter = ParameterGenerator::fromReflection(
            new ParameterReflection(['ZendTest\Code\Generator\TestAsset\CallableTypeHintClass', 'foo'], 'bar')
        );

        $this->assertEquals('callable', $parameter->getType());
    }

    /**
     * @dataProvider dataFromReflectionGenerate
     * @param string $methodName
     * @param string $expectedCode
     */
    public function testFromReflectionGenerate($methodName, $expectedCode)
    {
        $reflectionParameter = $this->getFirstReflectionParameter($methodName);
        $codeGenParam = ParameterGenerator::fromReflection($reflectionParameter);

        $this->assertEquals($expectedCode, $codeGenParam->generate());
    }

    public function dataFromReflectionGenerate()
    {
        return [
            ['name', '$param'],
            ['type', '\\stdClass $bar'],
            ['reference', '&$baz'],
            ['defaultValue', '$value = \'foo\''],
            ['defaultNull', '$value = null'],
            ['fromArray', 'array $array'],
            ['hasNativeDocTypes', '$integer'],
            ['defaultArray', '$array = array()'],
            ['defaultArrayWithValues', '$array = array(1, 2, 3)'],
            ['defaultFalse', '$val = false'],
            ['defaultTrue', '$val = true'],
            ['defaultZero', '$number = 0'],
            ['defaultNumber', '$number = 1234'],
            ['defaultFloat', '$float = 1.34'],
            ['defaultConstant', '$con = \'foo\'']
        ];
    }

    /**
     * @param  string                               $method
     * @return \Zend\Code\Reflection\ParameterReflection
     */
    protected function getFirstReflectionParameter($method)
    {
        $reflectionClass = new \Zend\Code\Reflection\ClassReflection(
            'ZendTest\Code\Generator\TestAsset\ParameterClass'
        );
        $method = $reflectionClass->getMethod($method);

        $params = $method->getParameters();

        return array_shift($params);
    }

    public function testCreateFromArray()
    {
        $parameterGenerator = ParameterGenerator::fromArray([
            'name'              => 'SampleParameter',
            'type'              => 'int',
            'defaultvalue'      => 'foo',
            'passedbyreference' => false,
            'position'          => 1,
            'sourcedirty'       => false,
            'sourcecontent'     => 'foo',
            'indentation'       => '-',
        ]);

        $this->assertEquals('SampleParameter', $parameterGenerator->getName());
        $this->assertEquals('int', $parameterGenerator->getType());
        $this->assertInstanceOf('Zend\Code\Generator\ValueGenerator', $parameterGenerator->getDefaultValue());
        $this->assertFalse($parameterGenerator->getPassedByReference());
        $this->assertEquals(1, $parameterGenerator->getPosition());
        $this->assertFalse($parameterGenerator->isSourceDirty());
        $this->assertEquals('foo', $parameterGenerator->getSourceContent());
        $this->assertEquals('-', $parameterGenerator->getIndentation());
    }

    /**
     * @group 4988
     */
    public function testParameterGeneratorReturnsCorrectTypeForNonNamespaceClasses()
    {
        require_once __DIR__ . '/../TestAsset/NonNamespaceClass.php';

        $reflClass = new \Zend\Code\Reflection\ClassReflection('ZendTest_Code_NsTest_BarClass');
        $params = $reflClass->getMethod('fooMethod')->getParameters();

        $param = ParameterGenerator::fromReflection($params[0]);

        $this->assertEquals('\ZendTest_Code_NsTest_BarClass', $param->getType());
    }

    /**
     * @group 5193
     */
    public function testTypehintsWithNamespaceInNamepsacedClassReturnTypewithBackslash()
    {
        require_once __DIR__ . '/TestAsset/NamespaceTypeHintClass.php';

        $reflClass = new \Zend\Code\Reflection\ClassReflection('Namespaced\TypeHint\Bar');
        $params = $reflClass->getMethod('method')->getParameters();

        $param = ParameterGenerator::fromReflection($params[0]);

        $this->assertEquals('\OtherNamespace\ParameterClass', $param->getType());
    }

    /**
     * @group 6023
     *
     * @coversNothing
     */
    public function testGeneratedParametersHaveEscapedDefaultValues()
    {
        $parameter = new ParameterGenerator();

        $parameter->setName('foo');
        $parameter->setDefaultValue("\\'");
        $parameter->setType('stdClass');

        $this->assertSame("stdClass \$foo = '\\\\\\''", $parameter->generate());
    }
}
