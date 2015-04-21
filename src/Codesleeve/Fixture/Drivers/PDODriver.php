<?php

namespace Codesleeve\Fixture\Drivers;

use Codesleeve\Fixture\KeyGenerators\KeyGeneratorInterface;
use Codesleeve\Fixture\KeyGenerators\Crc32KeyGenerator;
use Illuminate\Support\Str;
use PDO;

class PDODriver
{
    /**
     * A PDO connection instance.
     *
     * @var PDO
     */
     protected $db;

    /**
     * An array of tables that have had fixture data loaded into them.
     *
     * @var array
     */
     protected $tables = [];

     /**
      * An instance of Laravel's Str class.
      *
      * @var Str
      */
     protected $str;

    /**
     * An instance of a key generator
     *
     * @var KeyGeneratorInterface
     */
     protected $keyGenerator;


     /**
     * Constructor method
     *
     * @param  DatabaseManager $db
     * @param  KeyGeneratorInterface $keyGenerator
     */
    public function __construct(PDO $pdo, KeyGeneratorInterface $keyGenerator = null)
    {
        if ($keyGenerator === null) {
            $keyGenerator = new Crc32KeyGenerator();
        }

        $this->str = new Str();
        $this->db = $pdo;
        $this->keyGenerator = $keyGenerator;
    }
    /**
     * Truncate a table.
     */
    public function truncate()
    {
        foreach (array_unique($this->tables) as $table) {
            $this->db->query("DELETE FROM $table");
        }

        $this->tables = [];
    }

    /**
     * Generate an integer hash of a string.
     * We'll use this method to convert a fixture's name into the
     * primary key of it's corresponding database table record.
     *
     * @param string $value - This should be the name of the fixture.
     *
     * @return int
     */
    protected function generateKey($value)
    {
        return $this->keyGenerator->generateKey($value);
    }
}
