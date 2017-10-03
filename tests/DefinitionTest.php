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
use sebastianthiel\DI\Resolver\Resolver;
use sebastianthiel\DI\Tests\_support\Mocks\BuildInParameterClass;
use sebastianthiel\DI\Tests\_support\Mocks\CircularDependencyAClass;
use sebastianthiel\DI\Tests\_support\Mocks\DefinitionMock;
use sebastianthiel\DI\Tests\_support\Mocks\ResolverMock;
use sebastianthiel\DI\Tests\_support\Mocks\FactoryInstanceClass;
use sebastianthiel\DI\Tests\_support\Mocks\InvokableClass;
use sebastianthiel\DI\Tests\_support\Mocks\SimpleClass;
use sebastianthiel\DI\Tests\_support\Mocks\SimpleClassInterface;
use sebastianthiel\DI\Tests\_support\Mocks\StaticFactoryClass;

class DefinitionTest extends Unit
{
    public function validDefinitionsProvider()
    {
        return [
            [SimpleClassInterface::class, SimpleClass::class, null, null, Definition::TYPE_ALIAS],
            [SimpleClassInterface::class, SimpleClass::class, Definition::TYPE_ALIAS, null, Definition::TYPE_ALIAS],
            [SimpleClassInterface::class, SimpleClass::class, null, new Resolver(), Definition::TYPE_ALIAS],
            [SimpleClassInterface::class, SimpleClass::class, Definition::TYPE_ALIAS, new Resolver(), Definition::TYPE_ALIAS],

            [SimpleClassInterface::class, function () { }, null, new Resolver(), Definition::TYPE_FACTORY],
            [SimpleClassInterface::class, StaticFactoryClass::class . '::build', null, new Resolver(), Definition::TYPE_FACTORY],
            [SimpleClassInterface::class, new InvokableClass(), null, new Resolver(), Definition::TYPE_FACTORY],
            [SimpleClassInterface::class, function () { }, Definition::TYPE_FACTORY, new Resolver(), Definition::TYPE_FACTORY],
            [SimpleClassInterface::class, StaticFactoryClass::class . '::build', Definition::TYPE_FACTORY, new Resolver(), Definition::TYPE_FACTORY],
            [SimpleClassInterface::class, new InvokableClass(), Definition::TYPE_FACTORY, new Resolver(), Definition::TYPE_FACTORY],

            [SimpleClassInterface::class, new SimpleClass(), null, null, Definition::TYPE_INSTANCE],
            [SimpleClassInterface::class, new SimpleClass(), Definition::TYPE_INSTANCE, null, Definition::TYPE_INSTANCE],
            [SimpleClassInterface::class, new SimpleClass(), null, new Resolver(), Definition::TYPE_INSTANCE],
            [SimpleClassInterface::class, new SimpleClass(), Definition::TYPE_INSTANCE, new Resolver(), Definition::TYPE_INSTANCE],

        ];
    }

    /**
     * @dataProvider validDefinitionsProvider
     */
    public function testConstruct($classId, $definition, $type, $resolver, $expectedType)
    {
        $definition = new Definition($classId, $definition, $type, $resolver);

        $this->assertEquals($expectedType, $definition->getType());
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
            [SimpleClassInterface::class, function () { }],
        ];
    }

    public function invalidDefinitionsProvider()
    {
        return [
            [SimpleClass::class, SimpleClass::class, null, null],
            ['Foo', SimpleClass::class, null, null],
            [SimpleClass::class, 'Foo', null, null],
            ['Foo', 'Foo', null, null],
            ['Foo', function () { }, null, null],
            [SimpleClass::class, function () { }, null, null],
            ['Foo', function () { }, Definition::TYPE_FACTORY, null],
            [SimpleClass::class, function () { }, Definition::TYPE_FACTORY, null],
        ];
    }

    /**
     * @expectedException \sebastianthiel\DI\Exceptions\InvalidArgumentException
     *
     * @dataProvider invalidDefinitionsProvider
     */
    public function testInvalidConstruct($classId, $definition, $type, $resolver)
    {
        new Definition($classId, $definition, $type, $resolver);
    }

}
