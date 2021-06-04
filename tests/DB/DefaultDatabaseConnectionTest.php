<?php
/*
 * This file is part of DbUnit.
 *
 * (c) Wang Zhong Hao <wzhhao@cn.ibm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\Framework\TestCase;

class DefaultDatabaseConnectionTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $this->db = new PDO(PHPUNIT_TESTSUITE_EXTENSION_DATABASE_DB2_DSN, PHPUNIT_TESTSUITE_EXTENSION_DATABASE_DB2_USERNAME, PHPUNIT_TESTSUITE_EXTENSION_DATABASE_DB2_PASSWORD);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::CASE_LOWER);
        $this->db->exec('CREATE TABLE phpunit.test (field1 VARCHAR(100))');
    }

    public function testRowCountForEmptyTableReturnsZero(): void
    {
        $conn = new DefaultConnection($this->db, PHPUNIT_TESTSUITE_EXTENSION_DATABASE_DB2_SCHEMA);
        $this->assertEquals(0, $conn->getRowCount('phpunit.test'));
    }

    public function testRowCountForTableWithTwoRowsReturnsTwo(): void
    {
        $this->db->exec('INSERT INTO phpunit.test (field1) VALUES (\'foobar\')');
        $this->db->exec('INSERT INTO phpunit.test (field1) VALUES (\'foobarbaz\')');

        $conn = new DefaultConnection($this->db);
        $this->assertEquals(2, $conn->getRowCount('phpunit.test'));
    }


    protected function tearDown(): void
    {
        $this->db->exec('DROP TABLE phpunit.test');
    }
}
