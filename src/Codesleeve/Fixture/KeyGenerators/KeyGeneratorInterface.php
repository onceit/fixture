<?php

namespace Codesleeve\Fixture\KeyGenerators;

/**
 * Generates a key for a given value.
 *  */
interface KeyGeneratorInterface
{
    /**
     * Generate a cache key for a given value.
     *
     * @param mixed $value The label to generate the key from
     *
     * @return mixed The generated key
     */
    public function generateKey($value);
}
