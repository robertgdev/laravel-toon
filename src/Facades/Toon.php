<?php

namespace  RobertGDev\LaravelToon\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string encode(mixed $input, ?\RobertGDev\Toon\Types\EncodeOptions $options = null)
 * @method static mixed decode(string $input, ?\RobertGDev\Toon\Types\DecodeOptions $options = null)
 *
 * @see \RobertGDev\LaravelToon\Toon
 */
class Toon extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'toon';
    }
}