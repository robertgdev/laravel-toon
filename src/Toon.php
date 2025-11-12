<?php

namespace  RobertGDev\LaravelToon;

use HelgeSverre\Toon\Toon as BaseToon;
use HelgeSverre\Toon\DecodeOptions;
use HelgeSverre\Toon\EncodeOptions;

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
        $indent = config('toon.encode.indent', 2);
        $indent = is_numeric($indent) ? (int)$indent : 2;
        
        $delimiter = config('toon.encode.delimiter', ',');
        if (!is_string($delimiter)) {
            $delimiter = ',';
        }
        
        $lengthMarker = config('toon.encode.lengthMarker', false);
        $lengthMarker = $this->parseLengthMarker($lengthMarker);

        return new EncodeOptions($indent, $delimiter, $lengthMarker);
    }

    /**
     * Get default decode options from Laravel config.
     */
    protected function getDefaultDecodeOptions(): DecodeOptions
    {
        $indent = config('toon.decode.indent', 2);
        $indent = is_numeric($indent) ? (int)$indent : 2;
        
        $strict = config('toon.decode.strict', true);
        $strict = is_bool($strict) || $strict === 'true' || $strict === 1;
        
        return new DecodeOptions($indent, $strict);
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