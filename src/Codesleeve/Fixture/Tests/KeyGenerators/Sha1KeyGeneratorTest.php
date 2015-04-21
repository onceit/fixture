<?php

namespace Codesleeve\Fixture\Test\KeyGenerators;

use Codesleeve\Fixture\KeyGenerators\Sha1KeyGenerator;

class Sha1KeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsValidKey()
    {
        $generator = new Sha1KeyGenerator();

        $key = $generator->generateKey('foo');

        $this->assertEquals(10, strlen($key));
        $this->assertEquals(6812387308, $key);
    }

    public function testReturnsValidKeyWithCustomLength()
    {
        $generator = new Sha1KeyGenerator(8);

        $this->assertEquals(8, strlen($generator->generateKey('foo')));
    }
}
