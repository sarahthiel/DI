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

namespace sebastianthiel\DI\Definition;

use sebastianthiel\DI\Exceptions\InvalidArgumentException;
use sebastianthiel\DI\Resolver\ResolverInterface;

/**
 * Class Definition
 *
 * @package sebastianthiel\DI
 */
class Definition implements DefinitionInterface
{
    /** @var  string */
    protected $classId;

    /** @var  mixed */
    protected $definition;

    /** @var  integer */
    protected $type;

    /**
     * Definition constructor.
     *
     * @param                                  $classId
     * @param                                  $definition
     * @param int|null                         $type
     * @param ResolverInterface|null $Resolver
     */
    public function __construct(string $classId, $definition, ?int $type = null, ?ResolverInterface $Resolver = null)
    {
        switch ($type) {
            case DefinitionInterface::TYPE_ALIAS:
                $this->setAlias($classId, $definition);
                break;
            case DefinitionInterface::TYPE_FACTORY:
                if (!$Resolver) {
                    throw new InvalidArgumentException('Resolver needed for definition type factory');
                }
                $this->setFactory($classId, $definition, $Resolver);
                break;
            case DefinitionInterface::TYPE_INSTANCE:
                $this->setInstance($classId, $definition);
                break;
            default:
                $this->set($classId, $definition, $Resolver);
        }
    }

    /**
     * @return mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }

    protected function set(string $classId, $value, ?ResolverInterface $Resolver = null) : void
    {
        if (!$this->validateClassId($classId)) {
            throw new InvalidArgumentException(sprintf('$classId has to be a valid class or interface name! "%s" given', $classId));
        }

        if (is_string($value) && $this->validateClassId($value)) {
            $this->setAlias($classId, $value);

            return;
        }
        if ($this->isCallable($value)) {
            if (!$Resolver) {
                throw new InvalidArgumentException('Resolver needed for definition type factory');
            }

            $this->setFactory($classId, $value, $Resolver);

            return;
        }
        if ($value instanceof $classId) {
            $this->setInstance($classId, $value);

            return;
        }

        throw new InvalidArgumentException('Invalid $value has to be a valid classname or callable');
    }

    /**
     * add a new alias definition
     *
     * @param string $sourceClassId source class / interface name
     * @param string $targetClassId target class name
     */
    protected function setAlias(string $sourceClassId, string $targetClassId) : void
    {
        if (!$this->validateClassId($sourceClassId)) {
            throw new InvalidArgumentException(sprintf('$sourceClassId has to be a valid class or interface name! "%s" given', $sourceClassId));
        }
        if (!$this->validateClassId($targetClassId)) {
            throw new InvalidArgumentException(sprintf('$targetClassId has to be a valid class or interface name! "%s" given', $targetClassId));
        }
        if ($sourceClassId == $targetClassId) {
            throw new InvalidArgumentException('$sourceClassId and $targetClassId can not be the same');
        }

        $this->classId = $sourceClassId;
        $this->definition = $targetClassId;
        $this->type = DefinitionInterface::TYPE_ALIAS;
    }

    /**
     * add a new factory definition
     *
     * @param string                      $classId  class / interface name
     * @param callable|string             $callable function / function name used as factory method
     * @param ResolverInterface $Resolver
     */
    protected function setFactory(string $classId, $callable, ResolverInterface $Resolver) : void
    {
        if (!$this->validateClassId($classId)) {
            throw new InvalidArgumentException(sprintf('$classId has to be a valid class or interface name! "%s" given', $classId));
        }
        if (!$this->isCallable($callable)) {
            throw new InvalidArgumentException('$callable has to be a valid callable!');
        }

        $this->classId = $classId;
        $this->definition = is_callable($callable) && is_object($callable) ? $callable : $Resolver->reflectCallable($callable);
        $this->type = DefinitionInterface::TYPE_FACTORY;
    }

    /**
     * @param string $classId
     * @param        $class
     */
    protected function setInstance(string $classId, $class) : void
    {
        if (!$this->validateClassId($classId)) {
            throw new InvalidArgumentException(sprintf('$classId has to be a valid class or interface name! "%s" given', $classId));
        }
        if (!$class instanceof $classId) {
            throw new InvalidArgumentException(sprintf('$class has to be an instance of "%s"', $classId));
        }

        $this->classId = $classId;
        $this->definition = $class;
        $this->type = DefinitionInterface::TYPE_INSTANCE;
    }

    /**************************/
    /*    Helper Functions   */
    /*************************/

    /**
     * check if a class id is valid
     *
     * @param string $classId
     *
     * @return bool
     */
    protected function validateClassId(string $classId) : bool
    {
        return (bool) (class_exists($classId) || interface_exists($classId));
    }

    /**
     * check if a provided value is a valid callable or can be converted into
     *
     * @param $callable
     *
     * @return bool
     */
    protected function isCallable($callable) : bool
    {
        switch (true) {
            case is_callable($callable):
            case is_string($callable) && function_exists($callable):
                return true;
                break;
            case is_string($callable) && strpos($callable, '::'):
                return is_callable(explode('::', $callable, 2));
                break;
        }

        return false;
    }
}
