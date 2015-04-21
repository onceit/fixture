<?php

namespace Codesleeve\Fixture\Tests\Drivers;

use Codesleeve\Fixture\Fixture;
use Codesleeve\Fixture\Drivers\Eloquent;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Codesleeve\Fixture\Exceptions\InvalidHasOneRelationException;
use Codesleeve\Fixture\Exceptions\InvalidHasManyRelationException;
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

    public function testTimestampsAreAutofilled()
    {
        $this->fixture->up(['pirates']);

        $this->assertInstanceOf('\DateTime', $this->fixture->pirates('redbeard')->created_at);
        $this->assertInstanceOf('\DateTime', $this->fixture->pirates('blackbeard')->updated_at);
    }

    public function testBelongsToRelationsArePopulated()
    {
        $this->fixture->up(['parrots', 'pirates']);

        $this->assertEquals(959118195, $this->fixture->parrots('george')->pirate_id);
    }

    public function testBelongsToManyRelationsArePopulated()
    {
        $this->fixture->up(['pirates', 'catchphrases']);
        
        $this->assertCount(3, $this->fixture->pirates('blackbeard')->catchphrases);
        $this->assertEquals(
            ['361330166', '361067094', '497172778'],
            $this->fixture->pirates('blackbeard')->catchphrases->lists('id')
        );
    }

    public function testThrowsExceptionOnHasOne()
    {
        $this->fixture->setConfig([
            'location' => __DIR__ . '/../Fixtures/invalid'
        ]);

        $this->setExpectedException(
            'Codesleeve\Fixture\Exceptions\InvalidHasOneRelationException',
            'Can\'t set a HasOne relation on pirates set a BelongsTo relation on parrots instead.'
        );

        $this->fixture->up(['pirates']);
    }

    public function testThrowExceptionOnHasMany()
    {
        $this->fixture->setConfig([
            'location' => __DIR__ . '/../Fixtures/invalid'
        ]);

        $this->setExpectedException(
            'Codesleeve\Fixture\Exceptions\InvalidHasManyRelationException',
            'Can\'t set a HasMany relation on boats set a BelongsTo relation on crew instead.'
        );

        $this->fixture->up(['boats', 'crew']);
    }

    public function tearDown()
    {
        $this->db->exec(file_get_contents(
            __DIR__ . '/Fixtures/teardown.sql'
        ));
    }
}
