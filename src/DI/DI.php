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

use sebastianthiel\DI\Definition\Definition;
use sebastianthiel\DI\Definition\DefinitionInterface;
use sebastianthiel\DI\Exceptions\CircularException;
use sebastianthiel\DI\Exceptions\InvalidArgumentException;
use sebastianthiel\DI\Resolver\Resolver;
use sebastianthiel\DI\Resolver\ResolverInterface;

/**
 * Class DI
 */
class DI implements DIInterface
{
    protected $Resolver;

    /** @var string[] */
    protected $processing = [];

    /** @var Definition[] */
    protected $definitions = [];

    public function __construct()
    {
        $this->Resolver = new Resolver();
        $this->Resolver->setDDI($this);

        $this->setInstance(static::class, $this);
        $this->setAlias(DIInterface::class, static::class);
    }

    /**************************/
    /*    Setter Functions   */
    /*************************/

    /**
     * add new definition to DI
     *
     * @param string $classId has to be a valid class or interface name
     * @param mixed  $value
     */
    public function set(string $classId, $value) : void
    {
        $this->addNewDefinition($classId, $value);
    }

    /**
     * add a new alias definition
     *
     * @param string $sourceClassId source class / interface name
     * @param string $targetClassId target class name
     */
    public function setAlias(string $sourceClassId, string $targetClassId) : void
    {
        $this->addNewDefinition($sourceClassId, $targetClassId, DefinitionInterface::TYPE_ALIAS);
    }

    /**
     * add a new factory definition
     *
     * @param string          $classId  class / interface name
     * @param callable|string $callable function / function name used as factory method
     */
    public function setFactory(string $classId, $callable) : void
    {
        $this->addNewDefinition($classId, $callable, DefinitionInterface::TYPE_FACTORY);
    }

    /**
     * @param string $classId
     * @param        $class
     */
    public function setInstance(string $classId, $class) : void
    {
        $this->addNewDefinition($classId, $class, DefinitionInterface::TYPE_INSTANCE);
    }

    /**
     * create a new definition object and add it to the list
     *
     * @param          $classId
     * @param          $definition
     * @param int|null $type
     */
    protected function addNewDefinition($classId, $definition, $type = null) : void
    {
        $this->remove($classId);

        $this->definitions[$classId] = new Definition($classId, $definition, $type, $this->Resolver);
    }

    /**************************/
    /*    Getter Functions   */
    /*************************/

    /**
     * get an object from DI
     *
     * @param string $classId
     * @param array  $arguments constructor arguments
     *
     * @return object
     */
    public function get($classId, array $arguments = [])
    {
        if (isset($this->processing[$classId])) {
            throw new CircularException(sprintf("Circular dependency detected. Stacktrace:\n%s", implode("\n", $this->processing)));
        }

        $result = null;
        $this->processing[$classId] = true;

        if (isset($this->definitions[$classId])) {
            $type = $this->definitions[$classId]->getType();
            switch ($type) {
                case DefinitionInterface::TYPE_ALIAS:
                    $result = $this->get($this->definitions[$classId]->getDefinition(), $arguments);
                    break;
                case DefinitionInterface::TYPE_FACTORY:
                    $result = $this->invoke($this->definitions[$classId]->getDefinition(), $arguments);
                    break;
                case DefinitionInterface::TYPE_INSTANCE:
                    $result = $this->definitions[$classId]->getDefinition();
                    break;
                default:
            }
        }
        if (!$result) {
            $result = $this->Resolver->getInstance($classId, $arguments);
        }

        unset($this->processing[$classId]);

        return $result;
    }

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
    public function has($classId) : bool
    {
        return class_exists($classId) || (interface_exists($classId) && isset($this->definitions[$classId]));
    }

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
    public function hasDefinition(string $classId) : bool
    {
        return isset($this->definitions[$classId]);
    }

    /**
     * completely remove an item from the DI
     *
     * @param string $classId
     */
    public function remove(string $classId) : void
    {
        if (isset($this->definitions[$classId])) {
            unset($this->definitions[$classId]);
        }
    }

    public function invoke($callable, array $arguments = [], $object = null)
    {

        return $this->Resolver->invoke($callable, $arguments, $object);
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }


    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
