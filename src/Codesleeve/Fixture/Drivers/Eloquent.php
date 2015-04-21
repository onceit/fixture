<?php

namespace Codesleeve\Fixture\Drivers;

use Codesleeve\Fixture\KeyGenerators\KeyGeneratorInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use PDO;

class Eloquent extends PDODriver implements DriverInterface
{

    /**
     * The namespace of which to look for classes in
     *
     * @var string
     */
    protected $namespace;

    /**
     * {@inheritDoc}
     * @param  string $namespace
     */
    public function __construct(PDO $pdo, KeyGeneratorInterface $keyGenerator = null, $namespace = '')
    {
        $this->namespace = rtrim($namespace, '\\');

        parent::__construct($pdo, $keyGenerator);
    }

    /**
     * Resolves the fully qualified name of table's corresponding model
     *
     * @param  string $tableName
     * @return string
     *
     * @throws InvalidArgumentException if the class cant be resolved
     */
    private function resolveModelClass($tableName)
    {
        $className = $this->str->studly(
            $this->str->singular($tableName)
        );

        $fullyQualified = $this->namespace . '\\' . $className;

        if (class_exists($fullyQualified)) {
            return $fullyQualified;
        };

        throw new InvalidArgumentException(sprintf('Can\'t resolve a class for %s', $tableName));
    }

    /**
     * {@inheritDoc}
     */
    public function buildRecords($tableName, array $fixtures)
    {
        array_push($this->tables, $tableName);

        $className = $this->resolveModelClass($tableName);

        foreach ($fixtures as $label => &$fixture) {
            $fixture = $this->buildRecord($className, $label, $fixture);
        }

        return $fixtures;
    }

    /**
     * Build an induvidual fixture's record
     *
     * @param string $className The class name of the model to build
     * @param string $label     The label of the fixture
     * @param array  $fixture   A key => values array to build the record with
     *
     * @return Model
     */
    protected function buildRecord($className, $label, array $fixture)
    {
        $record = new $className();
        $primaryKey = $record->getKeyName();

        // Generate this record's primary key. If its not set.
        if (!isset($record->$primaryKey)) {
            $record->$primaryKey = $this->generateKey($label);
        }

        foreach ($fixture as $column => $value) {
            $attr = $this->str->camel($column);

            // If a column name exists as a method on the model, we will just assume
            // it is a relationship and we'll generate the primary key for it and store
            // it as a foreign key on the model.
            if (method_exists($record, $attr) && $record->$attr() instanceof Relation) {
                $this->evaluateRelation($record, $record->$attr(), $value);

                continue;
            }

            $record->$attr = $value;
        }

        return $record;
    }

    /**
     * Evalutes the relation on a model and tries to populate it
     *
     * @param Model    $record   An instace of the record the relation is on
     * @param Relation $relation An instance of the relation
     * @param string   $value    The value of the relation
     */
    protected function evaluateRelation(Model $record, Relation $relation, $value)
    {
        if ($relation instanceof BelongsTo) {
            $this->populateBelongsTo($record, $relation, $value);

            return;
        }
    }

    /**
     * Populates a belongs to value
     *
     * @param Model     $record   An instace of the record the relation is on
     * @param BelongsTo $relation An instance of the relation
     * @param string    $value    The value of the relation
     */
    protected function populateBelongsTo(Model $record, BelongsTo $relation, $value)
    {
        $foreignKey = $relation->getForeignKey();
        $record->$foreignKey = $this->generateKey($value);
    }

    /**
     * Persist the fixtures to the database
     *
     * @param  array $fixtures The fixtures to persist
     *
     * @return array           The persisted records
     */
    protected function persist($fixtures)
    {
        array_walk($fixtures, function(&$fixture){
            $fixture->save();
        });

        return $fixtures;
    }
}
