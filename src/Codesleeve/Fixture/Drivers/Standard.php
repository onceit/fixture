<?php

namespace Codesleeve\Fixture\Drivers;



class Standard extends PDODriver implements DriverInterface
{
    /**
     * Build a fixture record using the passed in values.
     *
     * @param  string $tableName  The table name to populate the fixtures with
     * @param  array $fixtures    An array of key => value arrays to build the records with
     * @return array
     */
    public function buildRecords($tableName, array $fixtures)
    {
        array_push($this->tables, $tableName);

        foreach ($fixtures as $label => &$fixture) {
            $fixture = $this->buildRecord($label, $fixture);
        }

        return $this->persist($tableName, $fixtures);
    }

    protected function buildRecord($label, $fixture)
    {
        // Generate this record's primary key. If its not set.
        if (!isset($fixture['id'])) {
            $fixture['id'] = $this->generateKey($label);
        }

        foreach ($fixture as $key => &$value) {
            if ($this->str->endsWith($key, '_id')) {
                $value = $this->generateKey($value);
            }
        }

        return $fixture;
    }

    protected function persist($tableName, array $fixtures)
    {
        foreach ($fixtures as &$fixture) {
            $placeholders = array_fill(0, count($fixture), '?');
            $columns = array_keys($fixture);

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $tableName,
                implode(',', $columns),
                implode(',', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute($fixture);
            $fixture = (object) $fixture;
        }

        return $fixtures;
    }
}
