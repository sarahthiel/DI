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
use sebastianthiel\DI\Exceptions\InvalidArgumentException;
use sebastianthiel\DI\Resolver\ResolverInterface;
use sebastianthiel\DI\Resolver\Resolver;

class DIFactory
{
    public function createDI()
    {
        return new DI();
    }
}
