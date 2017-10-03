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

namespace sebastianthiel\DI;

use Psr\Container\ContainerInterface;

interface DIInterface extends \ArrayAccess, ContainerInterface
{
    /**
     * add new definition to DI
     *
     * @param string $classId has to be a valid class or interface name
     * @param mixed  $value
     */
    public function set(string $classId, $value) : void;

    /**
     * add a new alias definition
     *
     * @param string $sourceClassId source class / interface name
     * @param string $targetClassId target class name
     */
    public function setAlias(string $sourceClassId, string $targetClassId) : void;

    /**
     * add a new factory definition
     *
     * @param string          $classId  class / interface name
     * @param callable|string $callable function / function name used as factory method
     */
    public function setFactory(string $classId, $callable) : void;

    /**
     * @param string $classId
     * @param        $class
     */
    public function setInstance(string $classId, $class) : void;

    /**
     * get an object from DI
     *
     * @param string $classId
     *
     * @return object
     */
    public function get($classId);

    /**
     * resolve arguments and invoke a callable
     *
     * @param            $callable
     * @param array      $arguments default values
     * @param mixed|null $object
     *
     * @return mixed
     */
    public function invoke($callable, array $arguments = [], $object = null);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $classId Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($classId) : bool;

    /**
     * Returns true if the container has a definition for the given identifier
     *
     * `hasDefinition($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $classId Identifier of the entry to look for.
     *
     * @return bool
     */
    public function hasDefinition(string $classId) : bool;

    /**
     * completely remove an item from the DI
     *
     * @param string $classId
     */
    public function remove(string $classId) : void;
}
