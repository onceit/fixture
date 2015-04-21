<?php

namespace Codesleeve\Fixture\Drivers;

class Standard extends PDODriver implements DriverInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildRecords($tableName, array $fixtures)
    {
        array_push($this->tables, $tableName);

        foreach ($fixtures as $label => &$fixture) {
            $fixture = $this->buildRecord($label, $fixture);
        }

        return $this->persist($tableName, $fixtures);
    }

    /**
     * Build an individual fixture
     *
     * @param string $label   The label of the fixture
     * @param array  $fixture A key => values array to build the fixture with
     *
     * @return array
     */
    protected function buildRecord($label, $fixture)
    {
        // Generate this record's primary key. If its not set.
        if (!isset($fixture['id'])) {
            $fixture['id'] = $this->generateKey($label);
        }

        foreach ($fixture as $key => &$value) {
            // If the value is a function call it and set the attribute to the result
            if (is_callable($value)) {
                $value = $value($fixture);
            }

            if ($this->str->endsWith($key, '_id')) {
                $value = $this->generateKey($value);
            }
        }

        return $fixture;
    }

    /**
     * Persist the fixtures to the database
     *
     * @param  string $tableName The table to persist to
     * @param  array  $fixtures  The fixtures to persist
     *
     * @return array           The persisted records
     */
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
