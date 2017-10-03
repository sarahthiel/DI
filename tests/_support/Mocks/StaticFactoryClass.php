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

class StaticFactoryClass
{
    public static function build()
    {
        return new SimpleClass();
    }

    public static function resolveClassname(SimpleClass $obj)
    {
        return $obj;
    }

    public static function resolveOptionalParameter(string $string = '')
    {
        return (object) ['value' => $string];
    }
}
