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

/**
 * Interface DefinitionInterface
 *
 * @package sebastianthiel\DI
 */
interface DefinitionInterface
{
    const TYPE_OTHER    = 0;
    const TYPE_ALIAS    = 1;
    const TYPE_FACTORY  = 2;
    const TYPE_INSTANCE = 3;

    public function __construct(string $classId, $definition, ?int $type = null);

    /**
     * @return mixed
     */
    public function getDefinition();

    /**
     * @return mixed
     */
    public function getType() : int;
}
