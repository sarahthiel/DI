<?php
/**
 * sebastianthiel DI
 *
 * @package    sebastianthiel/DI
 * @author     Sebastian Thiel <me@sebastian-thiel.eu>
 * @license    https://opensource.org/licenses/MIT  MIT
 * @version    0.1
 */

declare(strict_types=1);

namespace sebastianthiel\DI\Tests;

use Codeception\Test\Unit;
use sebastianthiel\DI\Definition\Definition;
use sebastianthiel\DI\DI;
use sebastianthiel\DI\DIFactory;
use sebastianthiel\DI\Tests\_support\Mocks\BuildInParameterClass;
use sebastianthiel\DI\Tests\_support\Mocks\CircularDependencyAClass;
use sebastianthiel\DI\Tests\_support\Mocks\DefinitionMock;
use sebastianthiel\DI\Tests\_support\Mocks\ResolverMock;
use sebastianthiel\DI\Tests\_support\Mocks\FactoryInstanceClass;
use sebastianthiel\DI\Tests\_support\Mocks\InvokableClass;
use sebastianthiel\DI\Tests\_support\Mocks\SimpleClass;
use sebastianthiel\DI\Tests\_support\Mocks\SimpleClassInterface;
use sebastianthiel\DI\Tests\_support\Mocks\StaticFactoryClass;

class DITest extends Unit
{
    protected function getProperty($object, $name)
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);

        return $property;
    }

    /**
     * DI->setAlias()
     */
    public function testSetAlias()
    {
        $DI = (new DIFactory())->createDI();

        $definitions = $this->getProperty($DI, 'definitions');

        //via setAlias Method
        $DI->setAlias(SimpleClassInterface::class, SimpleClass::class);
        $this->assertTrue(array_key_exists(SimpleClassInterface::class, $definitions->getValue($DI)));
        $this->assertEquals(SimpleClass::class, $definitions->getValue($DI)[SimpleClassInterface::class]->getDefinition());
        $this->assertEquals(Definition::TYPE_ALIAS, $definitions->getValue($DI)[SimpleClassInterface::class]->getType());
    }

    public function setAliasInvalidArgumentsProvider()
    {
        return [
            [SimpleClass::class, SimpleClass::class],
            ['Foo', SimpleClass::class],
            [SimpleClassInterface::class, 'Foo'],
            ['Foo', 'Foo']
        ];
    }

    /**
     * @param $classId
     * @param $value
     *
     * @dataProvider setAliasInvalidArgumentsProvider
     *
     * @expectedException  \sebastianthiel\DI\Exceptions\InvalidArgumentException
     */
    public function testSetAliasInvalidArguments($classId, $value)
    {
        $DI = (new DIFactory())->createDI();
        $DI->setAlias($classId, $value);
    }

    /**
     * DI->setFactory()
     */
    public function setFactoryProvider()
    {
        return [
            [function () { }, \Closure::class],
            [StaticFactoryClass::class . '::build', \ReflectionFunctionAbstract::class],
            [new InvokableClass(), InvokableClass::class]
        ];
    }

    /**
     * @param $factory
     * @param $storedType
     *
     * @dataProvider setFactoryProvider
     */
    public function testSetFactory($factory, $storedType)
    {
        $DI = (new DIFactory())->createDI();

        $definitions = $this->getProperty($DI, 'definitions');

        //via setFactory Method
        $DI->setFactory(SimpleClassInterface::class, $factory);

        $this->assertTrue(array_key_exists(SimpleClassInterface::class, $definitions->getValue($DI)));
        $this->assertInstanceOf($storedType, $definitions->getValue($DI)[SimpleClassInterface::class]->getDefinition());
        $this->assertEquals(Definition::TYPE_FACTORY, $definitions->getValue($DI)[SimpleClassInterface::class]->getType());
    }

    public function setFactoryInvalidArgumentsProvider()
    {
        return [
            ['notAClassname', function () { }],
            [SimpleClassInterface::class, 'notACallable'],
            [SimpleClassInterface::class, 'unknownClass::method'],
        ];
    }

    /**
     * @param $classId
     * @param $factory
     *
     * @dataProvider setFactoryInvalidArgumentsProvider
     *
     * @expectedException  \sebastianthiel\DI\Exceptions\InvalidArgumentException
     */
    public function testSetFactoryInvalidArguments($classId, $factory)
    {
        $DI = (new DIFactory())->createDI();

        $DI->setFactory($classId, $factory);
    }

    /**
     * DI->setInstance()
     */
    public function setInstanceProvider()
    {
        return [
            [new SimpleClass()]
        ];
    }

    /**
     * @param $instance
     *
     * @dataProvider setInstanceProvider
     */
    public function testSetInstance($instance)
    {
        $DI = (new DIFactory())->createDI();

        $definitions = $this->getProperty($DI, 'definitions');

        //via setFactory Method
        $DI->setInstance(SimpleClassInterface::class, $instance);
        $this->assertTrue(array_key_exists(SimpleClassInterface::class, $definitions->getValue($DI)));
        $this->assertEquals($instance, $definitions->getValue($DI)[SimpleClassInterface::class]->getDefinition());
        $this->assertEquals(Definition::TYPE_INSTANCE, $definitions->getValue($DI)[SimpleClassInterface::class]->getType());
    }

    public function setInstanceInvalidArgumentsProvider()
    {
        return [
            ['notAClassname', new SimpleClass()],
            [SimpleClassInterface::class, new StaticFactoryClass()],
        ];
    }

    /**
     * @param $classId
     * @param $instance
     *
     * @dataProvider setInstanceInvalidArgumentsProvider
     *
     * @expectedException  \sebastianthiel\DI\Exceptions\InvalidArgumentException
     */
    public function testSetInstanceInvalidArguments($classId, $instance)
    {
        $DI = (new DIFactory())->createDI();

        $DI->setInstance($classId, $instance);
    }

    /**
     * DI->set()
     */
    public function setProvider()
    {
        return [
            [SimpleClassInterface::class, new SimpleClass(), Definition::TYPE_INSTANCE],
            [SimpleClass::class, new SimpleClass(), Definition::TYPE_INSTANCE],
            [SimpleClassInterface::class, SimpleClass::class, Definition::TYPE_ALIAS],
            [SimpleClass::class, function () { }, Definition::TYPE_FACTORY],
            [SimpleClass::class, function () { }, Definition::TYPE_FACTORY],
            [SimpleClass::class, StaticFactoryClass::class . '::build', Definition::TYPE_FACTORY],
            [SimpleClass::class, new InvokableClass(), Definition::TYPE_FACTORY]
        ];
    }

    /**
     * @param $classId
     * @param $value
     * @param $expectedType
     *
     * @dataProvider setProvider
     */
    public function testSet($classId, $value, $expectedType)
    {
        $DI = (new DIFactory())->createDI();

        $definitions = $this->getProperty($DI, 'definitions');

        //via setFactory Method
        $DI->set($classId, $value);

        $this->assertTrue(array_key_exists($classId, $definitions->getValue($DI)));
        $this->assertEquals($expectedType, $definitions->getValue($DI)[$classId]->getType());
    }

    public function setInvalidArgumentsProvider()
    {
        return [
            [SimpleClass::class, SimpleClass::class],
            ['Foo', SimpleClass::class],
            [SimpleClassInterface::class, 'Foo'],
            ['Foo', 'Foo'],
            ['notAClassname', function () { }],
            [SimpleClassInterface::class, 'notACallable'],
            ['notAClassname', new SimpleClass()],
            [SimpleClassInterface::class, new StaticFactoryClass()],
        ];
    }

    /**
     * @param $classId
     * @param $instance
     *
     * @dataProvider setInvalidArgumentsProvider
     *
     * @expectedException  \sebastianthiel\DI\Exceptions\InvalidArgumentException
     */
    public function testSetInvalidArguments($classId, $instance)
    {
        $DI = (new DIFactory())->createDI();

        $DI->set($classId, $instance);
    }

    /**
     * DI->invoke()
     */
    public function invokeProvider()
    {
        return [
            [[StaticFactoryClass::class, 'resolveOptionalParameter'], [], null, (object) ['value' => '']],
            [[StaticFactoryClass::class, 'resolveOptionalParameter'], ['string' => 'Foo'], null, (object) ['value' => 'Foo']],
            [[StaticFactoryClass::class, 'resolveClassname'], [], null, new SimpleClass()],
            [[FactoryInstanceClass::class, 'resolveOptionalParameter'], [], null, (object) ['value' => '']],
            [[FactoryInstanceClass::class, 'resolveOptionalParameter'], ['string' => 'Foo'], null, (object) ['value' => 'Foo']],
            [[FactoryInstanceClass::class, 'resolveClassname'], [], null, new SimpleClass()],
            [[FactoryInstanceClass::class, 'resolveOptionalParameter'], [], new FactoryInstanceClass(), (object) ['value' => '']],
            [[FactoryInstanceClass::class, 'resolveOptionalParameter'], ['string' => 'Foo'], new FactoryInstanceClass(), (object) ['value' => 'Foo']],
            [[FactoryInstanceClass::class, 'resolveClassname'], [], new FactoryInstanceClass(), new SimpleClass()]
        ];
    }

    /**
     * @param $callable
     * @param $arguments
     * @param $object
     * @param $expected
     *
     * @dataProvider invokeProvider
     */
    public function testInvoke($callable, $arguments, $object, $expected)
    {
        $DI = (new DIFactory())->createDI();
        $result = $DI->invoke($callable, $arguments, $object);
        $this->assertEquals($expected, $result);
    }

    public function invokeInvalidAttributesProvider()
    {
        return [
            ['Foo', [], null]
        ];
    }

    /**
     * @param $callable
     * @param $arguments
     * @param $object
     *
     * @dataProvider invokeInvalidAttributesProvider
     *
     * @expectedException  \sebastianthiel\DI\Exceptions\InvalidArgumentException
     */
    public function testInvokeInvalidAttributes($callable, $arguments, $object)
    {
        $DI = (new DIFactory())->createDI();
        $DI->invoke($callable, $arguments, $object);
    }

    /**
     * DI->get()
     */
    public function getProvider()
    {
        return [
            [SimpleClass::class, new SimpleClass(), [], new SimpleClass()],
            [SimpleClass::class, function () { return 'Foo'; }, [], 'Foo'],
            [SimpleClass::class, function ($value) { return $value; }, ['value' => 'Foo'], 'Foo'],
            [SimpleClass::class, StaticFactoryClass::class . '::build', [], new SimpleClass()],
            [SimpleClassInterface::class, StaticFactoryClass::class . '::resolveClassname', [], new SimpleClass()],
            [SimpleClassInterface::class, StaticFactoryClass::class . '::resolveOptionalParameter', [], (object) ['value' => '']],
            [SimpleClassInterface::class, StaticFactoryClass::class . '::resolveOptionalParameter', ['string' => 'Foo'], (object) ['value' => 'Foo']],
            [SimpleClass::class, new InvokableClass(), [], new SimpleClass()],
            [SimpleClassInterface::class, SimpleClass::class, [], new SimpleClass()],
            [SimpleClass::class, 'trim', ['str' => 'Foo '], 'Foo'],
            [BuildInParameterClass::class, BuildInParameterClass::class, ['foo' => 'Foo'], new BuildInParameterClass('Foo')],
        ];
    }

    /**
     * @param $classId
     * @param $value
     * @param $arguments
     * @param $expectedResult
     *
     * @dataProvider getProvider
     */
    public function testGet($classId, $value, $arguments, $expectedResult)
    {
        $DI = (new DIFactory())->createDI();
        if ($classId != $value) {
            $DI->set($classId, $value);
        }

        $result = $DI->get($classId, $arguments);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @expectedException \sebastianthiel\DI\Exceptions\CircularException
     */
    public function testGetCircular()
    {
        $DI = (new DIFactory())->createDI();
        $DI->get(CircularDependencyAClass::class);
    }

    /**
     * @return array
     */
    public function getInvalidArgumentsProvider()
    {
        return [
            [SimpleClassInterface::class, []],
            [BuildInParameterClass::class, []],
        ];
    }

    /**
     * @expectedException \sebastianthiel\DI\Exceptions\InvalidArgumentException
     *
     * @param $class
     * @param $arguments
     *
     * @dataProvider getInvalidArgumentsProvider
     */
    public function testGetInvalid($class, $arguments)
    {
        $DI = (new DIFactory())->createDI();
        $DI->get($class, $arguments);
    }

    /**
     * DI->has()
     */
    public function testHas()
    {
        $DI = (new DIFactory())->createDI();
        $this->assertTrue($DI->has(SimpleClass::class));
        $this->assertFalse($DI->has(SimpleClassInterface::class));
        $this->assertFalse($DI->has('Foo'));
    }

    /**
     * DI->hasDefinition()
     */
    public function testHasDefinition()
    {
        $DI = (new DIFactory())->createDI();
        $this->assertFalse($DI->hasDefinition(SimpleClass::class));
        $DI->set(SimpleClass::class, new SimpleClass());
        $this->assertTrue($DI->hasDefinition(SimpleClass::class));
    }

    /**
     * DI->remove()
     */
    public function testRemove()
    {
        $DI = (new DIFactory())->createDI();

        $definitions = $this->getProperty($DI, 'definitions');

        $DI->setAlias(SimpleClassInterface::class, SimpleClass::class);
        $this->assertTrue(array_key_exists(SimpleClassInterface::class, $definitions->getValue($DI)));

        $DI->remove(SimpleClassInterface::class);
        $this->assertFalse(array_key_exists(SimpleClassInterface::class, $definitions->getValue($DI)));
    }

    public function testArrayAccess()
    {
        $DI = (new DIFactory())->createDI();

        $this->assertFalse(isset($DI[SimpleClassInterface::class]));

        $DI[SimpleClassInterface::class] = SimpleClass::class;
        $this->assertTrue(isset($DI[SimpleClassInterface::class]));

        $obj = $DI[SimpleClassInterface::class];
        $this->assertEquals(new SimpleClass(), $obj);

        unset($DI[SimpleClassInterface::class]);
        $this->assertFalse(isset($DI[SimpleClassInterface::class]));
    }
}
