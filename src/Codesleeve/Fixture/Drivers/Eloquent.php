<?php

namespace Codesleeve\Fixture\Drivers;

use Codesleeve\Fixture\Exceptions\InvalidHasOneRelationException;
use Codesleeve\Fixture\Exceptions\InvalidHasManyRelationException;
use Codesleeve\Fixture\KeyGenerators\KeyGeneratorInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * {@inheritDoc}
     */
    public function buildRecords($tableName, array $fixtures)
    {
        array_push($this->tables, $tableName);

        $className = $this->resolveModelClass($tableName);

        foreach ($fixtures as $label => &$fixture) {
            $fixture = $this->buildRecord($className, $label, $fixture);
        }

        return $this->persist($fixtures);
    }

    /**
     * Resolves the fully qualified name of table's corresponding model
     *
     * @param  string $tableName
     *
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
     * Build an individual fixture
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

            // If the value is a function call it and set the attribute to the result
            if (is_callable($value)) {
                $record->$attr = $value($record);

                continue;
            }

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
     * Evaluates the relation on a model and tries to populate it
     *
     * @param Model    $record   An instance of the record the relation is on
     * @param Relation $relation An instance of the relation
     * @param string   $value    The value of the relation
     */
    protected function evaluateRelation(Model $record, Relation $relation, $value)
    {
        if ($relation instanceof BelongsTo) {
            return $this->populateBelongsTo($record, $relation, $value);
        }

        if ($relation instanceof BelongsToMany) {
            return $this->populateBelongsToMany($record, $relation, $value);
        }

        if ($relation instanceof HasOne) {
            throw new InvalidHasOneRelationException(sprintf(
                'Can\'t set a HasOne relation on %s set a BelongsTo relation on %s instead.',
                $record->getTable(),
                explode('.', $relation->getForeignKey())[0]
            ));
        }

        if ($relation instanceof HasMany) {
            throw new InvalidHasManyRelationException(sprintf(
                'Can\'t set a HasMany relation on %s set a BelongsTo relation on %s instead.',
                $record->getTable(),
                explode('.', $relation->getForeignKey())[0]
            ));
        }
    }

    /**
     * Populates a belongs to value
     *
     * @param Model     $record   An instance of the record the relation is on
     * @param BelongsTo $relation An instance of the relation
     * @param string    $value    The value of the relation
     */
    protected function populateBelongsTo(Model $record, BelongsTo $relation, $value)
    {
        $foreignKey = $relation->getForeignKey();
        $record->$foreignKey = $this->generateKey($value);
    }

    protected function populateBelongsToMany(Model $record, BelongsToMany $relation, $value)
    {
        // If the record has not yet been saved save it
        if (!$record->exists) {
            $record->save();
        }

        $sync = [];
        foreach ($value as $key => $value) {
            // Create an attribute array with the id as the key if there are pivot attributes
            if (is_array($value)) {
                $sync[$this->generateKey($key)] = $value;
                continue;
            }

            $sync[] = $this->generateKey($value);
        }

        // Persist relations
        $relation->sync($sync);
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
        foreach ($fixtures as &$fixture) {
            $fixture->save();
        }

        return $fixtures;
    }
}
