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
     * Constructor method
     *
     * @param  DatabaseManager $db
     * @param  KeyGeneratorInterface $keyGenerator
     */
    public function __construct(PDO $pdo, KeyGeneratorInterface $keyGenerator = null, $namespace = '')
    {
        $this->namespace = rtrim($namespace, '\\');

        parent::__construct($pdo, $keyGenerator);
    }

    /**
     * Resolves the fully qualified name of table's corresponding model
     *
     * @param string $tableName
     * @return string
     *
     * @throws InvalidArgumentException if the class cant be resolved
     */
    private function resolveModel($tableName)
    {
        $className = $this->str->studly(
            $this->str->singular($tableName)
        );

        $fullyQualified = $this->namespace . '\\' . $className;

        if (class_exists($fullyQualified)){
            return $fullyQualified;
        };

        throw new InvalidArgumentException(sprintf('Can\'t resolve a class for %s', $tableName));
    }

    /**
     * Build a fixture record using the passed in values.
     *
     * @param  string $tableName
     * @param  array $records
     * @return array
     */
    public function buildRecords($tableName, array $records)
    {
        $insertedRecords = [];
        $this->tables[$tableName] = $tableName;

        foreach ($records as $recordName => $recordValues) {
            $model = $this->resolveModel($tableName);
            $record = new $model;
            $primaryKey = $record->getKeyName();

            // Generate this record's primary key. If its not set.
            if (!isset($recordValues[$primaryKey])) {
                $recordValues[$primaryKey] = $this->generateKey($recordName);
            }

            foreach ($recordValues as $columnName => $columnValue) {
                $camelKey = camel_case($columnName);

                // If a column name exists as a method on the model, we will just assume
                // it is a relationship and we'll generate the primary key for it and store
                // it as a foreign key on the model.
                if (method_exists($record, $camelKey)) {
                    $this->insertRelatedRecords($recordName, $record, $camelKey, $columnValue);

                    continue;
                }

                if (is_callable($columnValue)) {
                    $columnValue = call_user_func($columnValue, $record, $recordValues);
                }

                $record->$columnName = $columnValue;
            }

            $record->save();
            $insertedRecords[$recordName] = $record;
        }

        return $insertedRecords;
    }

    /**
     * Insert related records for a fixture.
     *
     * @param  string $recordName
     * @param  Model $record
     * @param  string $camelKey
     * @param  string $columnValue
     * @return void
     */
    protected function insertRelatedRecords($recordName, Model $record, $camelKey, $columnValue)
    {
        $relation = $record->$camelKey();

        if ($relation instanceof BelongsTo) {
            $this->insertBelongsTo($record, $relation, $columnValue);

            return;
        }

        if ($relation instanceof BelongsToMany) {
            $this->insertBelongsToMany($recordName, $relation, $columnValue);

            return;
        }
    }

    /**
     * Insert a belongsTo foreign key relationship.
     *
     * @param  Model $record
     * @param  Relation $relation
     * @param  int $columnValue
     * @return void
     */
    protected function insertBelongsTo(Model $record, Relation $relation, $columnValue)
    {
        $foreignKeyName = $relation->getForeignKey();
        $foreignKeyValue = $this->generateKey($columnValue);
        $record->$foreignKeyName = $foreignKeyValue;
    }

    /**
     * Insert a belongsToMany foreign key relationship.
     *
     * @param  string recordName
     * @param  Relation $relation
     * @param  int $columnValue
     * @return void
     */
    protected function insertBelongsToMany($recordName, Relation $relation, $columnValue)
    {
        $joinTable = $relation->getTable();
        $this->tables[] = $joinTable;
        $relatedRecords = explode(',', str_replace(', ', ',', $columnValue));

        foreach ($relatedRecords as $relatedRecord) {
            list($fields, $values) = $this->buildBelongsToManyRecord($recordName, $relation, $relatedRecord);
            $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
            $sql = "INSERT INTO $joinTable ($fields) VALUES ($placeholders)";
            $sth = $this->db->prepare($sql);
            $sth->execute($values);
        }
    }

    /**
     * Parse the fixture data for belongsToManyRecord.
     * The current syntax allows for pivot data to be provided
     * via a pipe delimiter with colon separated key values.
     * <code>
     *    'Travis' => [
     *        'first_name'   => 'Travis',
     *        'last_name'    => 'Bennett',
     *        'roles'		 => 'endUser|foo:bar, root'
     *    ]
     * </code>
     *
     * @param  string $recordName The name of the relation the fixture is defined on (e.g Travis).
     * @param  Relation $relation The relationship oject (should be of type belongsToMany).
     * @param  string $relatedRecord The related record data (e.g endUser|foo:bar or root).
     * @return array
     */
    protected function buildBelongsToManyRecord($recordName, Relation $relation, $relatedRecord)
    {
        $pivotColumns = explode('|', $relatedRecord);
        $relatedRecordName = array_shift($pivotColumns);

        $foreignKeyPieces = explode('.', $relation->getForeignKey());
        $foreignKeyName = $foreignKeyPieces[1];
        $foreignKeyValue = $this->generateKey($recordName);

        $otherKeyPieces = explode('.', $relation->getOtherKey());
        $otherKeyName = $otherKeyPieces[1];
        $otherKeyValue = $this->generateKey($relatedRecordName);

        $fields = "$foreignKeyName, $otherKeyName";
        $values = [$foreignKeyValue, $otherKeyValue];

        foreach ($pivotColumns as $pivotColumn) {
            list($columnName, $columnValue) = explode(':', $pivotColumn);
            $fields .= ", $columnName";
            $values[] = $columnValue;
        }

        return [$fields, $values];
    }
}
