<?php

namespace RobertGDev\LaravelToon\Contracts;

interface Toonable
{
    /**
     * Convert the object to its TOON representation.
     */
    public function toToon(): string;
}