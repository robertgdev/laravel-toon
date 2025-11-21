<?php

namespace RobertGDev\LaravelToon\Concerns;

use HelgeSverre\Toon\EncodeOptions;


trait ToonFormat
{
    /**
     * Convert the object to its TOON representation.
     */
    public function toToon(?EncodeOptions $options = null): string
    {
        return app('toon')->encode($this, $options);
    }
}