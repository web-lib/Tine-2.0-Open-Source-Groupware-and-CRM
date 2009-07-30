<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Setup
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Jonas Fischer <j.fischer@metaways.de>
 * @version     $Id: SqlTest.php 1703 2008-04-03 18:16:32Z lkneschke $
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Setup_Backend_PdoOciTest::main');
}

/**
 * Test class for Tinebase_User
 */
class Setup_Backend_OracleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Setup_Backend_Oracle
     * @access protected
     */
    protected $_backend;
    
    /**
     * Array holding table names that should be deleted with {@see tearDown}
     * 
     * @var array
     */
    protected $_tableNames = array();
    
    /**
     * @var Setup_Backend_Schema_Table_Abstract
     */
    protected $_table;
    
    protected $_tableXml = '
            <table>
                <name>oracle_test</name>
                <version>1</version>
                <declaration>
                    <field>
                        <name>id</name>
                        <type>integer</type>
                        <autoincrement>true</autoincrement>
                    </field>
                    <field>
                        <name>name</name>
                        <type>text</type>
                        <length>128</length>
                        <notnull>true</notnull>
                    </field>
                    <index>
                        <name>id</name>
                        <primary>true</primary>
                        <field>
                            <name>id</name>
                        </field>
                    </index>
                </declaration>
            </table>';

    
    

    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite('Tine 2.0 Setup Backend Pdo_Oci Tests');
        PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->_backend = Setup_Backend_Factory::factory('Oracle');
        $this->_createTestTable();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        foreach ($this->_tableNames as $tableName) {
            try {
                $this->_backend->dropTable($tableName);
            }
            catch (Zend_Db_Statement_Exception $e) {
                //probably the table already was deleted by a test
            }
        }
    }

    
    
    
    
    public function testGetCreateStatement()
    {
        $expected = 'CREATE TABLE "' . SQL_TABLE_PREFIX. 'oracle_test" ('."\n".'  "id" NUMBER(11,0) NOT NULL,'."\n".'  "name" VARCHAR2(128) NOT NULL,'."\n".'CONSTRAINT "pk_' . $this->_table->name .'" PRIMARY KEY ("id")'."\n".')';
        $actual = $this->_backend->getCreateStatement(Setup_Backend_Schema_Table_Factory::factory('Xml', $this->_tableXml));

        $this->assertEquals($expected, $actual);
    }
    
    public function testTableExists()
    {
        $this->assertTrue($this->_backend->tableExists($this->_table->name));
        $this->_backend->dropTable($this->_table->name);
        $this->assertFalse($this->_backend->tableExists($this->_table->name));
    }
    
    public function testColumnExists()
    {
    	$columntName = 'testColumnExists';
        $string ="
                <field>
                    <name>$columntName</name>
                    <type>text</type>
                    <length>25</length>
                    <notnull>true</notnull>
                </field>";
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);

        $this->assertFalse($this->_backend->columnExists($columntName, $this->_table->name));
        $this->_backend->addCol($this->_table->name, $field);
        $this->assertTrue($this->_backend->columnExists($columntName, $this->_table->name));
    }
    
    public function testSequenceExists()
    {
        //Tests standard test table (with sequence)
        $this->assertTrue($this->_backend->sequenceExists($this->_table->name));

        //Tests table without sequence
        $tableXml = '
        <table>
            <name>oracle_seq_test</name>
            <version>1</version>
            <declaration>
                <field>
                    <name>name</name>
                    <type>text</type>
                    <length>128</length>
                    <notnull>true</notnull>
                </field>
            </declaration>
        </table>';
        $table = Setup_Backend_Schema_Table_Factory::factory('Xml', $tableXml);
        $this->_tableNames[] = $table->name;
        $this->_backend->createTable($table);
        $this->assertFalse($this->_backend->sequenceExists($table->name));
    }
    
    public function testGetExistingSchema()
    {
    	$schema = $this->_backend->getExistingSchema($this->_table->name);
    	
    	$this->assertEquals($this->_table->name, $schema->name, 'Test table name');
    	
    	$this->assertEquals(1, count($schema->indices));
    	$idIndex = $schema->indices[0];
        $this->assertEquals('true', $idIndex->notnull, 'Test $idIndex->notnull');
        $this->assertEquals('true', $idIndex->primary, 'Test $idIndex->primary');
        $this->assertEquals('true', $idIndex->autoincrement, 'Test $idIndex->auto_increment');
    	
    	$this->assertEquals(2, count($schema->fields));
    	$idField = $schema->fields[0];
    	$this->assertEquals('true', $idField->notnull, 'Test idField->notnull');
    	$this->assertEquals('true', $idField->primary, 'Test idField->primary');
    	$this->assertEquals('true', $idField->autoincrement, 'Test idField->auto_increment');
    	$this->assertTrue(empty($idField->unsigned), 'Test idField->unsigned');
    	
    }
    
    public function testAddCol() 
    {
        $string ="
                <field>
                    <name>testAddCol</name>
                    <type>text</type>
                    <length>25</length>
                    <notnull>true</notnull>
                </field>";
            
        $statement = $this->_fixFieldDeclarationString('"testAddCol" VARCHAR2(25) NOT NULL');    
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->assertEquals($statement, $this->_backend->getFieldDeclarations($field));

        $this->_backend->addCol($this->_table->name, $field);
        
        $this->setExpectedException('Setup_Backend_Exception_NotImplemented');
        
        $this->_backend->addCol($this->_table->name, $field, 1); //Cannot use 3rd parameter $_position in Oracle 
    }
    
    public function testStringToFieldStatement_001() 
    {
        $string ="
            <field>
                <name>id</name>
                <type>integer</type>
            </field>";
            
        $statement = $this->_fixFieldDeclarationString('"id" NUMBER(11,0)');    
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->assertEquals($statement, $this->_backend->getFieldDeclarations($field));
        
        $this->setExpectedException('Zend_Db_Statement_Exception', '1430'); //1060: Column "id" already exists - expecting Exception'
        $this->_backend->addCol($this->_table->name, $field);
        
    }

    public function testStringToFieldStatement_002() 
    {
        $string ="
            <field>
                <name>id2</name>
                <type>integer</type>
                <autoincrement>true</autoincrement>
            </field>";
            
        $statement = $this->_fixFieldDeclarationString('"id2" NUMBER(11,0) NOT NULL');    
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->assertEquals($statement, $this->_backend->getFieldDeclarations($field));
        
        $this->setExpectedException('Setup_Backend_Exception_NotImplemented');
        $this->_backend->addCol($this->_table->name, $field);
    }
    
    public function testStringToFieldStatement_003() 
    {
        $string ="
                <field>
                    <name>test</name>
                    <type>text</type>
                    <length>25</length>
                    <notnull>true</notnull>
                </field>";
            
        $statement = $this->_fixFieldDeclarationString('"test" VARCHAR2(25) NOT NULL');    
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->assertEquals($statement, $this->_backend->getFieldDeclarations($field));

        $this->_backend->addCol($this->_table->name, $field);
        
        $schema = $this->_backend->getExistingSchema($this->_table->name);
        $newColumn = end($schema->fields);
        $this->assertEquals('test', $newColumn->name);
        $this->assertEquals('25', $newColumn->length);
        $this->assertEquals('true', $newColumn->notnull);
        $this->assertEquals('text', $newColumn->type);
        $this->assertNotEquals('true', $newColumn->primary);
        $this->assertNotEquals('true', $newColumn->unique);
    }
    
    public function testStringToFieldStatement_004() 
    {
        $string ="
                 <field>
                    <name>test</name>
                    <type>enum</type>
                    <value>enabled</value>
                    <value>disabled</value>
                    <notnull>true</notnull>
                </field>";
   
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->_backend->addCol($this->_table->name, $field);
        $schema = $this->_backend->getExistingSchema($this->_table->name);
        $newColumn = end($schema->fields);
        $this->assertEquals(array('enabled', 'disabled'), $newColumn->value);
        $this->assertEquals('test', $newColumn->name);
        $this->assertEquals('true', $newColumn->notnull);       
        $this->assertEquals('enum', $newColumn->type);
        $this->assertNotEquals('true', $newColumn->primary);
        $this->assertNotEquals('true', $newColumn->unique);
        
        $db = Tinebase_Core::getDb();
        $db->insert(SQL_TABLE_PREFIX . $this->_table->name, array('name' => 'test', 'test' => 'enabled'));
        $this->setExpectedException('Zend_Db_Statement_Exception', '2290'); //invalid enum value -> expect exception
        $db->insert(SQL_TABLE_PREFIX . $this->_table->name, array('name' => 'test', 'test' => 'deleted'));
    }  
    
    public function testStringToFieldStatement_005() 
    {
        $string ="
                <field>
                    <name>order</name>
                    <type>integer</type>
                    <length>11</length>
                    <unsigned>true</unsigned>
                    <notnull>true</notnull>
                </field>";
            
        $statement = $this->_fixFieldDeclarationString("`order` int(11)  unsigned  NOT NULL");    
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        
        $this->_backend->addCol($this->_table->name, $field);
        
        $schema = $this->_backend->getExistingSchema($this->_table->name);
        $newColumn = end($schema->fields);
        $this->assertEquals('order', $newColumn->name);
        $this->assertEquals('true', $newColumn->notnull);       
        $this->assertEquals('integer', $newColumn->type);
        $this->assertFalse(isset($newColumn->unsigned)); //unsigned option is currently not supported by oracle adapter
        $this->assertNotEquals('true', $newColumn->primary);
        $this->assertNotEquals('true', $newColumn->unique);
    }
    
    public function testStringToFieldStatement_006() 
    {
        $string ="
                
                <field>
                    <name>last_login</name>
                    <type>datetime</type>
                </field>";
            
        $statement = $this->_fixFieldDeclarationString('"last_login" VARCHAR2(25)');    
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->assertEquals($statement, $this->_backend->getFieldDeclarations($field));
        
        $this->_backend->addCol($this->_table->name, $field);
    }
    
    public function testStringToFieldStatement_007() 
    {
        $string ="
                
                <field>
                    <name>email_sent</name>
                    <type>boolean</type>
                    <default>false</default>
                </field>";
            
        $statement = $this->_fixFieldDeclarationString('"email_sent" NUMBER(4,0) DEFAULT 0');    
        
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->assertEquals($statement, $this->_backend->getFieldDeclarations($field));
        
        $this->_backend->addCol($this->_table->name, $field);
    }
    
    public function testLongTableName() 
    {
        //Tests table without sequence
        $tableXml = '
        <table>
            <name>long_name_0123456789_0123456789</name>
            <version>1</version>
            <declaration>
                <field>
                    <name>name</name>
                    <type>text</type>
                    <length>128</length>
                    <notnull>true</notnull>
                </field>
            </declaration>
        </table>';
        $table = Setup_Backend_Schema_Table_Factory::factory('Xml', $tableXml);
        $this->_tableNames[] = $table->name;
        $this->setExpectedException('Zend_Db_Statement_Exception', '972'); //oracle identifiers cannot be longer than 30 characters 
        $this->_backend->createTable($table);
    }  
      
    public function testDatatypeTextReturnsPlainText() 
    {
        $string ="
                <field>
                    <name>test</name>
                    <type>text</type>
                </field>";
        $field = Setup_Backend_Schema_Field_Factory::factory('Xml', $string);
        $this->_backend->addCol($this->_table->name, $field);
        
        $schema = $this->_backend->getExistingSchema($this->_table->name);
        $newColumn = end($schema->fields);
        $this->assertEquals('text', $newColumn->type);
        $this->assertEquals('4000', $newColumn->length);
        
        $db = Tinebase_Core::getDb();
        $tableName = SQL_TABLE_PREFIX . $this->_table->name;
        $testValues = array(
            'some text',
            str_pad('test', 4001, 'x') 
        );
        $this->setExpectedException('Zend_Db_Statement_Exception', '1461'); //maximum length is 4000 characters
        foreach ($testValues as $index => $value) {
            $db->insert($tableName, array('name' => $index, 'test' => $value));
            $result = $db->fetchOne('SELECT "test" FROM "' . $tableName . '" WHERE "name"=?', array($index));
            $this->assertEquals($value, $result);
        }
    }
    
    protected function _createTestTable()
    {
        $this->_table = Setup_Backend_Schema_Table_Factory::factory('Xml', $this->_tableXml);
        $this->_tableNames[] = $this->_table->name;
        try {
            $this->_backend->createTable($this->_table);
        } catch (Zend_Db_Statement_Exception $e) {
        	$this->_backend->dropTable($this->_table->name);
        	$this->_backend->createTable($this->_table);
        }
    }

    /**
     * Perform some insignificant string format manipulations (add/remove Whitespace).
     * This is needed because the format of the return values of the tested methods 
     * has changed over time and might change again someday.
     * 
     * @param String $_value
     * @return String
     */
    protected function _fixFieldDeclarationString($_value) {
        $return = trim($_value);
        $return = str_replace('  ', ' ', $return);
        return '  ' . $return;
    }

    /**
     * Perform some insignificant string format manipulations (add/remove Whitespace).
     * This is needed because the format of the return values of the tested methods 
     * has changed over time and might change again someday.
     * 
     * @param String $_value
     * @return String
     */
    protected function _fixIndexDeclarationString($_value) {
        $return = trim($_value);
        return '  ' . $return;
    }
    
}        


                
if (PHPUnit_MAIN_METHOD == 'Setup_Backend_MysqlTest::main') {
    Setup_Backend_OracleTest::main();
}
