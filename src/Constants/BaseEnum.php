<?php

namespace HXM\LaravelPlanning\Constants;

use HXM\LaravelPlanning\Contracts\EnumInterface;

abstract class BaseEnum implements EnumInterface
{

    static function getValues(): array
    {
        return (new \ReflectionClass(get_called_class()))->getConstants();
    }
}
