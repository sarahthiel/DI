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

namespace sebastianthiel\DI\Tests\_support\Mocks;

class BuildInParameterClass
{
    protected $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }
}