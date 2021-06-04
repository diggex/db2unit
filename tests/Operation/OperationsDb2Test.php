<?php
/*
 * This file is part of DbUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use PHPUnit\DbUnit\DataSet\CompositeDataSet;
use PHPUnit\DbUnit\DataSet\DefaultDataSet;
use PHPUnit\DbUnit\DataSet\DefaultTable;
use PHPUnit\DbUnit\DataSet\DefaultTableMetadata;
use PHPUnit\DbUnit\DataSet\FlatXmlDataSet;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\Operation\Delete;
use PHPUnit\DbUnit\Operation\DeleteAll;
use PHPUnit\DbUnit\Operation\Insert;
use PHPUnit\DbUnit\Operation\Replace;
use PHPUnit\DbUnit\Operation\Truncate;
use PHPUnit\DbUnit\Operation\Update;

use PHPUnit\DbUnit\TestCase;

require_once \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'DatabaseTestUtility.php';

class Extensions_Database_Operation_OperationsDb2Test extends TestCase
{
    protected function setUp(): void
    {
        if (!\extension_loaded('pdo_ibm')) {
            $this->markTestSkipped('pdo_ibm is required to run this test.');
        }

        if (!\defined('PHPUNIT_TESTSUITE_EXTENSION_DATABASE_DB2_DSN')) {
            $this->markTestSkipped('No DB2 server configured for this test.');
        }

        parent::setUp();
    }

    public function getConnection()
    {
        $conn =  new DefaultConnection(DBUnitTestUtility::getDb2DB(), PHPUNIT_TESTSUITE_EXTENSION_DATABASE_DB2_SCHEMA);
        return $conn;
    }

    public function getDataSet()
    {
        $xml = new FlatXmlDataSet(__DIR__ . '/../_files/XmlDataSets/OperationsDb2TestFixture.xml');
        return $xml;
    }

    /**
     * @covers Truncate::execute
     */
    public function testTruncate(): void
    {
        $truncateOperation = new Truncate();
        $truncateOperation->execute($this->getConnection(), $this->getDataSet());

        $expectedDataSet = new DefaultDataSet([
            new DefaultTable(
                new DefaultTableMetadata(
                    'phpunit.table1',
                    ['table1_id', 'column1', 'column2', 'column3', 'column4']
                )
            ),
            new DefaultTable(
                new DefaultTableMetadata(
                    'phpunit.table2',
                    ['table2_id', 'table1_id', 'column5', 'column6', 'column7', 'column8']
                )
            ),
        ]);

        $this->assertDataSetsEqual($expectedDataSet, $this->getConnection()->createDataSet(['phpunit.table1','phpunit.table2']));
    }

    public function getCompositeDataSet()
    {
        $compositeDataset = new CompositeDataSet();

        $dataset = $this->createXMLDataSet(__DIR__ . '/../_files/XmlDataSets/TruncateCompositeDb2Test.xml');
        $compositeDataset->addDataSet($dataset);

        return $compositeDataset;
    }

    public function testTruncateComposite(): void
    {
        $truncateOperation = new Truncate();
        $truncateOperation->execute($this->getConnection(), $this->getCompositeDataSet());

        $expectedDataSet = new DefaultDataSet([
            new DefaultTable(
                new DefaultTableMetadata(
                    'phpunit.table1',
                    ['table1_id', 'column1', 'column2', 'column3', 'column4']
                )
            ),
            new DefaultTable(
                new DefaultTableMetadata(
                    'phpunit.table2',
                    ['table2_id', 'table1_id', 'column5', 'column6', 'column7', 'column8']
                )
            )
        ]);

        $this->assertDataSetsEqual($expectedDataSet, $this->getConnection()->createDataSet(['phpunit.table1','phpunit.table2']));
    }

}
