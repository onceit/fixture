<?php

namespace Codesleeve\Fixture\Tests\KeyGenerators;

use Codesleeve\Fixture\KeyGenerators\Crc32KeyGenerator;

class Crc32KeyGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsValidKey()
    {
        $generator = new Crc32KeyGenerator();

        $key = $generator->generateKey('foo');

        $this->assertEquals(9, strlen($key));
        $this->assertEquals(208889123, $key);
    }

    public function testMaxIdIsSet()
    {
        $this->assertTrue(defined('Codesleeve\\Fixture\\KeyGenerators\\MAX_ID'));
        $this->assertEquals(1073741823, constant('Codesleeve\\Fixture\\KeyGenerators\\MAX_ID'));
    }
}
