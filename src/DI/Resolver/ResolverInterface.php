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

namespace sebastianthiel\DI\Resolver;

use ReflectionFunctionAbstract;
use sebastianthiel\DI\DIInterface;

interface ResolverInterface
{

    /**
     * @param DIInterface $DI
     *
     * @return mixed
     */
    public function setDDI(DIInterface $DI) : void;

    /**
     * convert given value to a reflection function
     *
     * method isCallable must return true for the given value
     * otherwise this function will fail
     *
     * @param $callable
     *
     * @return \ReflectionFunctionAbstract
     */
    public function reflectCallable($callable) : ReflectionFunctionAbstract;

    /**
     * create a new class instance
     *
     * @param string $classId
     * @param array  $arguments
     *
     * @return object
     */
    public function getInstance(string $classId, array $arguments = []);

    /**
     * resolve arguments and invoke a callable
     *
     * @param                             $callable
     * @param array                       $arguments default values
     * @param mixed|null                  $object
     *
     * @return mixed
     */
    public function invoke($callable, array $arguments = [], $object = null);
}
