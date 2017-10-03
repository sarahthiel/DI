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
use sebastianthiel\DI\DIFactory;
use sebastianthiel\DI\Resolver\Resolver;
use sebastianthiel\DI\Tests\_support\Mocks\DefinitionMock;
use sebastianthiel\DI\Tests\_support\Mocks\ResolverMock;
use sebastianthiel\DI\Tests\_support\Mocks\SimpleClass;
use sebastianthiel\DI\Tests\_support\Mocks\SimpleClassInterface;

class DIFactoryTest extends Unit
{
    protected function getProperty($object, $name)
    {
        $property = new \ReflectionProperty($object, $name);
        $property->setAccessible(true);

        return $property;
    }

    public function testDefaultValues()
    {
        $factory = new DIFactory();
        $DI = $factory->createDI();

        $definitions = $this->getProperty($DI, 'definitions');
        $Resolver = $this->getProperty($DI, 'Resolver');

        $DI->set(SimpleClassInterface::class, SimpleClass::class);
        $this->assertInstanceOf(Definition::class, $definitions->getValue($DI)[SimpleClassInterface::class]);
        $this->assertInstanceOf(Resolver::class, $Resolver->getValue($DI));
    }
}
