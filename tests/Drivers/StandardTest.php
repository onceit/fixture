<?php

namespace Codesleeve\Fixture\Tests\Drivers;

use Codesleeve\Fixture\Fixture;
use Codesleeve\Fixture\Drivers\Standard;
use PDO;

class StandardTest extends \PHPUnit_Framework_TestCase
{
    protected $fixture;
    protected $db;

    public function setUp()
    {
        if ($this->fixture) {
            return;
        }

        $this->db = new PDO('sqlite::memory:');
        $this->db->exec(file_get_contents(
            __DIR__ . '/Fixtures/setup.sql'
        ));
        $this->fixture = new Fixture(
            [
                'location' => __DIR__ . '/../Fixtures/standard/'
            ],
            new Standard($this->db)
        );
    }

    public function testStableIdsDoNotGetOverwitten()
    {
        $this->fixture->up(['parrots']);

        $this->assertEquals(4, $this->fixture->parrots('polly')->id);
    }

    public function testIdsAreGeneratedForRecordsWithoutThem()
    {
        $this->fixture->up(['parrots']);

        $this->assertEquals(380982691, $this->fixture->parrots('george')->id);
    }

    public function testStaticAttributesAreSet()
    {
        $this->fixture->up(['pirates', 'parrots']);

        $this->assertEquals('Redbeard', $this->fixture->pirates('redbeard')->name);
        $this->assertEquals('Edward Teach', $this->fixture->pirates('blackbeard')->name);
        $this->assertEquals('King Louis', $this->fixture->parrots('louis')->name);
    }

    public function testCallableAttributesAreSet()
    {
        $this->fixture->up(['pirates']);

        $this->assertEquals('Edward Teach the Pirate!', $this->fixture->pirates('blackbeard')->title);
    }

    public function testBelongsToRelationsArePopulated()
    {
        $this->fixture->up(['parrots', 'pirates']);

        $this->assertEquals(959118195, $this->fixture->parrots('george')->pirate_id);
    }


    public function tearDown()
    {
        $this->db->exec(file_get_contents(
            __DIR__ . '/Fixtures/teardown.sql'
        ));
    }
}
