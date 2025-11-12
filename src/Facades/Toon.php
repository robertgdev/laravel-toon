<?php

namespace  RobertGDev\LaravelToon\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string encode(mixed $input, ?\HelgeSverre\Toon\EncodeOptions $options = null)
 * @method static mixed decode(string $input, ?\HelgeSverre\Toon\DecodeOptions $options = null)
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