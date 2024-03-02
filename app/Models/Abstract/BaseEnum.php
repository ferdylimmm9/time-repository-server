<?php

namespace App\Models\Abstract;

use BenSampo\Enum\Enum;

abstract class BaseEnum extends Enum
{

    public static function getInstances(): array
    {
        $constants = static::getConstants();
        $excludeConstants = static::getExcludedConstants();
        $finalConstants = array_diff($constants, $excludeConstants);

        return array_map(function ($key, $value) {
            return new static($value);
        }, array_keys($finalConstants), $finalConstants);
    }

    public static function getOptions(?array $options = null): array
    {
        return array_map(static function ($option) {
            return [
                'name'  => $option->description,
                'value' => $option->value,
            ];
        }, $options ?? self::getInstances());
    }

    protected static function getExcludedConstants(): array
    {
        return [];
    }
}

