<?php
/*
 * This file is part of DbUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\DbUnit\Database\Metadata;

/**
 * Provides functionality to retrieve meta data from an Oracle database.
 */
class Db2 extends AbstractMetadata
{
    /**
     * No character used to quote schema objects.
     *
     * @var string
     */
    protected $schemaObjectQuoteChar = '';

    /**
     * The command used to perform a TRUNCATE operation.
     *
     * @var string
     */
    protected $truncateCommand = 'TRUNCATE TABLE';

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $keys = [];

    /**
     * Returns an array containing the names of all the tables in the database.
     *
     * @return array
     */
    public function getTableNames()
    {
        $tableNames = [];

        $query = "SELECT NAME
                   FROM SYSIBM.SYSTABLES
                   WHERE TYPE='T' AND CREATOR='{$this->schema}'
                   ORDER BY NAME";


        $result = $this->pdo->query($query);

        while ($tableName = $result->fetchColumn(0)) {
            $tableNames[] = $tableName;
        }

        return $this->schema.".".$tableNames;
    }

    /**
     * Returns an array containing the names of all the columns in the
     * $tableName table,
     *
     * @param string $tableName
     *
     * @return array
     */
    public function getTableColumns($tableName)
    {
        if (!isset($this->columns[$tableName])) {
            $this->loadColumnInfo($tableName);
        }

        return $this->columns[$tableName];
    }

    /**
     * Returns an array containing the names of all the primary key columns in
     * the $tableName table.
     *
     * @param string $tableName
     *
     * @return array
     */
    public function getTablePrimaryKeys($tableName)
    {
        if (!isset($this->keys[$tableName])) {
            $this->loadColumnInfo($tableName);
        }

        return $this->keys[$tableName];
    }

    /**
     * Loads column info from a oracle database.
     *
     * @param string $tableName
     */
    protected function loadColumnInfo($tableName): void
    {
        $schemaQuery    = '';
        $primaryKeyQuery = ' AND c.KEYSEQ > 0';
        $tableParts    = $this->splitTableName($tableName);

        $this->columns[$tableName] = [];
        $this->keys[$tableName]    = [];

        if (!empty($tableParts['schema'])) {
            $schemaQuery    = " AND c.TABSCHEMA = '".strtoupper($tableParts['schema'])."'";
        }

        $query = "SELECT COLNAME
                    FROM SYSCAT.COLUMNS c 
                   WHERE c.TABNAME='" . strtoupper($tableParts['table']) . "'
                    $schemaQuery
                   ORDER BY COLNO ASC";

        $result = $this->pdo->query($query);

        while ($columnName = $result->fetchColumn(0)) {
            $this->columns[$tableName][] = strtolower($columnName);
        }

        $keyQuery = "SELECT COLNAME
                    FROM SYSCAT.COLUMNS c 
                   WHERE c.TABNAME='" . $tableParts['table'] . "'
                    $schemaQuery
                    $primaryKeyQuery 
                   ORDER BY COLNO";

        $result = $this->pdo->query($keyQuery);

        while ($columnName = $result->fetchColumn(0)) {
            $this->keys[$tableName][] = strtolower($columnName);
        }
    }
}
