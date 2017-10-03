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

use sebastianthiel\DI\DIInterface;
use sebastianthiel\DI\Exceptions\InvalidArgumentException;

class Resolver implements ResolverInterface
{
    protected $DI;

    public function setDDI(DIInterface $DI) : void
    {
        $this->DI = $DI;

        return;
    }

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
    public function reflectCallable($callable) : \ReflectionFunctionAbstract
    {
        if ($callable instanceof \ReflectionFunctionAbstract) {
            return $callable;
        } elseif (is_string($callable)) {
            if (function_exists($callable)) {
                return new \ReflectionFunction($callable);
            } elseif (strpos($callable, '::')) {
                return $this->reflectCallable(explode('::', $callable, 2));
            }
        } elseif (is_callable($callable)) {
            if (is_object($callable)) {
                return new \ReflectionMethod($callable, '__invoke');
            }
            if (is_array($callable) && (count($callable) === 2)) {
                return new \ReflectionMethod($callable[0], $callable[1]);
            }
        }

        throw new InvalidArgumentException('Invalid callable');
    }

    /**
     * automagicaly resolve parameters for a given callable
     *
     * @param array $parameters
     * @param array $defaults
     *
     * @return array
     */
    public function resolveParameters(array $parameters, array $defaults = []) : array
    {
        $arguments = [];

        foreach ($parameters as $index => $parameter) {
            /** @var \ReflectionClass $parameterClass */
            $parameterClass = $parameter->getClass();
            $parameterName = $parameter->getName();

            if (isset($defaults[$parameterName])) {
                $arguments[$index] = $defaults[$parameterName];
            } elseif (!$parameter->isOptional()) {
                if (!$parameterClass) {
                    throw new InvalidArgumentException(sprintf('Parameter "%s" is an buildIn type and can not be resolved', $parameterName));
                }

                $arguments[$index] = $this->DI->get($parameterClass->getName());
            }
        }

        return $arguments;
    }

    /**
     * create a new class instance
     *
     * @param string $classId
     * @param array  $arguments
     *
     * @return object
     */
    public function getInstance(string $classId, array $arguments = [])
    {
        $reflection = new \ReflectionClass($classId);

        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException(sprintf('Class "%s" is not instantiable', $classId));
        }

        $constructor = $reflection->getConstructor();
        if ($constructor) {
            $arguments = $this->resolveParameters($constructor->getParameters(), $arguments);

            return $reflection->newInstanceArgs($arguments);
        }

        return $reflection->newInstanceWithoutConstructor();
    }

    /**
     * resolve arguments and invoke a callable
     *
     * @param                             $callable
     * @param array                       $arguments default values
     * @param mixed|null                  $object
     *
     * @return mixed
     */
    public function invoke($callable, array $arguments = [], $object = null)
    {
        /** @var \ReflectionMethod $reflection */
        $reflection = $this->reflectCallable($callable);

        $arguments = $this->resolveParameters($reflection->getParameters(), $arguments);

        if ($reflection instanceof \ReflectionFunction) {
            /** @var \ReflectionFunction $reflection */
            return $reflection->invokeArgs($arguments);
        }
        if ($reflection->isStatic()) {
            return $reflection->invokeArgs(null, $arguments);
        }

        $reflectionClass = $reflection->getDeclaringClass()->getName();
        $object = is_object($object) && $object instanceof $reflectionClass
            ? $object
            : (is_callable($callable) && is_object($callable)
                ? $callable
                : $this->DI->get($reflectionClass));

        return $reflection->invokeArgs($object, $arguments);
    }
}
