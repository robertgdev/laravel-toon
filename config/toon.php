<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Encoding Options
    |--------------------------------------------------------------------------
    |
    | These options control how data is encoded to TOON format.
    |
    */

    'encode' => [
        // Number of spaces per indentation level
        'indent' => env('TOON_ENCODE_INDENT', 2),

        // Delimiter character for arrays and tabular data
        'delimiter' => env('TOON_ENCODE_DELIMITER', ','),

        // Length marker prefix (false or '#')
        'lengthMarker' => env('TOON_ENCODE_LENGTH_MARKER', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Decoding Options
    |--------------------------------------------------------------------------
    |
    | These options control how TOON format is decoded back to PHP data.
    |
    */

    'decode' => [
        // Number of spaces per indentation level
        'indent' => env('TOON_DECODE_INDENT', 2),

        // Enforce strict validation of array lengths and indentation
        'strict' => env('TOON_DECODE_STRICT', true),

        // Decode objects as StdClass instances instead of arrays
        'objectsAsStdClass' => env('TOON_DECODE_OBJECTS_AS_STDCLASS', false),
    ],

];