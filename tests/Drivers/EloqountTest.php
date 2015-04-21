<?php

namespace Codesleeve\Fixture\Tests\Drivers;

use Codesleeve\Fixture\Fixture;
use Codesleeve\Fixture\Drivers\Eloquent;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;

class EloquentTest extends \PHPUnit_Framework_TestCase
{
    protected $fixture;
    protected $db;

    public function setUp()
    {
        if ($this->fixture) {
            return;
        }

        $this->bootstrapEloquent();

        $this->db = $this->capsule->getConnection()->getPDO();
        $this->db->exec(file_get_contents(
            __DIR__ . '/Fixtures/setup.sql'
        ));
        $this->fixture = new Fixture(
            [
                'location' => __DIR__ . '/../Fixtures/orm/'
            ],
            new Eloquent(
                $this->db,
                null,
                'Codesleeve\\Fixture\\Tests\\Drivers\\Fixtures'
            )
        );
    }

    private function bootstrapEloquent()
    {
        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:'
        ], 'default');
        $this->capsule->bootEloquent();
    }

    public function testStableIds()
    {
        $this->fixture->up(['parrots']);

        $this->assertEquals(4, $this->fixture->parrots('polly')->id);
    }

    public function testAutogeneratedIds()
    {
        $this->fixture->up(['parrots']);

        $this->assertEquals(380982691, $this->fixture->parrots('george')->id);
    }

    public function testAttributes()
    {
        $this->fixture->up(['pirates']);

        $this->assertEquals('Avast!', $this->fixture->pirates('redbeard')->catchphrase);
        $this->assertEquals('Edward Teach', $this->fixture->pirates('blackbeard')->name);
    }

    public function testBelongsTo()
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