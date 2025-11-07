<?php

namespace  RobertGDev\LaravelToon;

use RobertGDev\Toon\Toon as BaseToon;
use RobertGDev\Toon\Types\DecodeOptions;
use RobertGDev\Toon\Types\EncodeOptions;

/**
 * Laravel wrapper for Toon encoding/decoding with config support.
 */
class Toon
{
    /**
     * Encode a value to TOON format using Laravel config defaults.
     */
    public function encode(mixed $input, ?EncodeOptions $options = null): string
    {
        $options = $options ?? $this->getDefaultEncodeOptions();
        return BaseToon::encode($input, $options);
    }

    /**
     * Decode TOON format to PHP using Laravel config defaults.
     */
    public function decode(string $input, ?DecodeOptions $options = null): mixed
    {
        $options = $options ?? $this->getDefaultDecodeOptions();
        return BaseToon::decode($input, $options);
    }

    /**
     * Get default encode options from Laravel config.
     */
    protected function getDefaultEncodeOptions(): EncodeOptions
    {
        return new EncodeOptions(
            indent: config('toon.encode.indent', 2),
            delimiter: config('toon.encode.delimiter', ','),
            lengthMarker: $this->parseLengthMarker(config('toon.encode.lengthMarker', false))
        );
    }

    /**
     * Get default decode options from Laravel config.
     */
    protected function getDefaultDecodeOptions(): DecodeOptions
    {
        return new DecodeOptions(
            indent: config('toon.decode.indent', 2),
            strict: config('toon.decode.strict', true),
            objectsAsStdClass: config('toon.decode.objectsAsStdClass', false)
        );
    }

    /**
     * Parse length marker value from config/env.
     */
    protected function parseLengthMarker(mixed $value): string|false
    {
        if ($value === false || $value === 'false' || $value === '' || $value === null) {
            return false;
        }
        
        if ($value === true || $value === 'true' || $value === '#') {
            return '#';
        }
        
        return false;
    }
}