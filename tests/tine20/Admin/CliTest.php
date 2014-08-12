<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Admin
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2009-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schüle <p.schuele@metaways.de>
 */

/**
 * Test helper
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';

/**
 * Test class for Tinebase_Admin
 */
class Admin_CliTest extends TestCase
{
    /**
     * Backend
     *
     * @var Admin_Frontend_Cli
     */
    protected $_cli;
    
    /**
     * @var array test objects
     */
    protected $objects = array();
    
    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    
    /**
     * config groups
     * 
     */
    protected $_testGroup = array();
    
    /**
     * @var Tinebase_Record_RecordSet
     */
    protected $_groupsToDelete = null;
    
    protected function setUp()
    {
        parent::setUp();
        
        
        $this->_cli = new Admin_Frontend_Cli();
        
        $this->_usernamesToDelete = array('hmaster', 'hmeister', 'hmoster', 'irmeli', 'testuser', 'm.muster');
        
        $this->_groupsToDelete = new Tinebase_Record_RecordSet('Tinebase_Model_Group');
        $this->_testGroup['domainuser'] = Tinebase_Group::getInstance()->create(new Tinebase_Model_Group(array(
            'name'   => 'domainuser'
        )));
        $this->_testGroup['teacher'] = Tinebase_Group::getInstance()->create(new Tinebase_Model_Group(array(
            'name'   => 'teacher'
        )));
        $this->_testGroup['student'] = Tinebase_Group::getInstance()->create(new Tinebase_Model_Group(array(
            'name'   => 'student'
        )));
        $this->_groupsToDelete->addRecord($this->_testGroup['teacher']);
        
        $this->objects['config'] = '<?xml version="1.0" encoding="UTF-8"?>
        <config>
            <dryrun>0</dryrun>
            <encoding>ISO-8859-1</encoding>
            <mapping>
                <field>
                    <source>firstname</source>
                    <destination>accountFirstName</destination>
                </field>
                <field>
                    <source>lastname</source>
                    <destination>accountLastName</destination>
                </field>
                <field>
                    <source>loginname</source>
                    <destination>accountLoginName</destination>
                </field>
                <field>
                    <source>password</source>
                    <destination>password</destination>
                </field>
                <field>
                    <source>email</source>
                    <destination>accountEmailAddress</destination>
                </field>
            </mapping>
        </config>';
        
        $this->objects['configWithHeadline'] = '<?xml version="1.0" encoding="UTF-8"?>
        <config>
            <headline>1</headline>
            <dryrun>0</dryrun>
            <encoding>ISO-8859-1</encoding>
            <mapping>
                <field>
                    <source>firstname</source>
                    <destination>accountFirstName</destination>
                </field>
                <field>
                    <source>lastname</source>
                    <destination>accountLastName</destination>
                </field>
                <field>
                    <source>loginname</source>
                    <destination>accountLoginName</destination>
                </field>
                <field>
                    <source>password</source>
                    <destination>password</destination>
                </field>
                <field>
                    <source>email</source>
                    <destination>accountEmailAddress</destination>
                </field>
            </mapping>
        </config>';
        
        $this->objects['configSemicolon'] = '<?xml version="1.0" encoding="UTF-8"?>
        <config>
            <model>Tinebase_Model_FullUser</model>
            <plugin>Admin_Import_User_Csv</plugin>
            <type>import</type>
            <headline>1</headline>
            <dryrun>0</dryrun>
            <extension>csv</extension>
            <delimiter>;</delimiter>
            <mapping>
            <field>
                    <source>firstname</source>
                    <destination>accountFirstName</destination>
                </field>
                <field>
                    <source>lastname</source>
                    <destination>accountLastName</destination>
                </field>
                <field>
                    <source>loginname</source>
                    <destination>accountLoginName</destination>
                </field>
                <field>
                    <source>password</source>
                    <destination>password</destination>
                </field>
                <field>
                    <source>email</source>
                    <destination>accountEmailAddress</destination>
                </field>
            </mapping>
        </config>';
        $this->objects['configEmailuser'] = '<?xml version="1.0" encoding="UTF-8"?>
        <config>
            <model>Tinebase_Model_FullUser</model>
            <plugin>Admin_Import_User_Csv</plugin>
            <type>import</type>
            <headline>1</headline>
            <dryrun>0</dryrun>
            <extension>csv</extension>
            <mapping>
                <field>
                    <source>firstname</source>
                    <destination>accountFirstName</destination>
                </field>
                <field>
                    <source>lastname</source>
                    <destination>accountLastName</destination>
                </field>
                <field>
                    <source>loginname</source>
                    <destination>accountLoginName</destination>
                </field>
                <field>
                    <source>password</source>
                    <destination>password</destination>
                </field>
                <field>
                    <source>email</source>
                    <destination>accountEmailAddress</destination>
                </field>
                <field>
                    <source>emailAliases</source> <!-- leerzeichen separator -->
                    <destination>emailAliases</destination>
                </field>
                <field>
                    <source>emailForwards</source>
                    <destination>emailForwards</destination>
                </field>
            </mapping>
        </config>';
          $this->objects['configAdvanced'] = '<?xml version="1.0" encoding="UTF-8"?>
        <config>
           <model>Tinebase_Model_FullUser</model>
           <plugin>Admin_Import_User_Csv</plugin>
           <type>import</type>
           <headline>1</headline>
           <dryrun>0</dryrun>
           <delimiter>,</delimiter>
           <mapping>
               <field>
                   <source>firstname</source>
                   <destination>accountFirstName</destination>
               </field>
               <field>
                   <source>lastname</source>
                   <destination>accountLastName</destination>
               </field>
               <field>
                   <source>objectname</source>
                   <destination>accountLoginName</destination>
               </field>
               <field>
                   <source>password</source>
                   <destination>password</destination>
               </field>
               <field>
                   <source>primary_group_id</source>
                   <destination>accountPrimaryGroup</destination>
               </field>
               <field>
                   <source>additional_groups</source>
                   <destination>groups</destination>
               </field>
              <field>
                   <source>accountHomeDirectory</source>
                   <destination>accountHomeDirectory</destination>
               </field>
               <field>
                   <source>accountHomeDirectoryPrefix</source>
                   <destination>accountHomeDirectoryPrefix</destination>
               </field>
               <field>
                   <source>accountLoginShell</source>
                   <destination>accountLoginShell</destination>
               </field>
               <field>
                   <source>homePath</source>
                   <destination>homePath</destination>
               </field>
               <field>
                   <source>homeDrive</source>
                   <destination>homeDrive</destination>
               </field>
               <field>
                   <source>logonScript</source>
                   <destination>logonScript</destination>
               </field>
               <field>
                   <source>profilePath</source>
                   <destination>profilePath</destination>
               </field>
           </mapping>
        </config>';
    }
    
    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        $this->_groupIdsToDelete = $this->_groupsToDelete->getArrayOfIds();
        parent::tearDown();
    }
    
    /**
     * test to import admin users
     *
     */
    public function testImportUsers()
    {
        $out = $this->_importUsers($this->objects['config'], dirname(__FILE__) . '/files/test.csv', 'admin_user_import_csv_test');
        $this->_checkResult($out);
    }
    
    /**
     * import users
     *
     * @param string $_config xml config
     * 
     * @see 0008300: Import User via CLI don't import all fields
     */
    protected function _importUsers($_config, $_filename, $_definition)
    {
        // create definition / check if exists
        try {
            $definition = Tinebase_ImportExportDefinition::getInstance()->getByName($_definition);
            $definition->plugin_options = $_config;
        } catch (Tinebase_Exception_NotFound $e) {
            $definition = Tinebase_ImportExportDefinition::getInstance()->create(new Tinebase_Model_ImportExportDefinition(array(
                'application_id'    => Tinebase_Application::getInstance()->getApplicationByName('Admin')->getId(),
                'name'              => $_definition,
                'type'              => 'import',
                'model'             => 'Tinebase_Model_FullUser',
                'plugin'            => 'Admin_Import_User_Csv',
                'plugin_options'    => $_config
            )));
        }
        
        $tempFilename = TestServer::replaceEmailDomainInFile($_filename);
        
        $opts = new Zend_Console_Getopt('abp:');
        $opts->setArguments(array($tempFilename, 'definition=' . $_definition));
        
        // start import (dry run)
        ob_start();
        $this->_cli->importUser($opts);
        $out = ob_get_clean();
        
        return $out;
    }
    
    /**
     * check import result
     * 
     * @param string $out
     */
    protected function _checkResult($out, $username = 'hmoster')
    {
        // check output
        if ($username == 'hmoster') {
            $this->assertEquals("Imported 3 records. Import failed for 0 records. \n", $out);
        } else {
            $this->assertEquals("Imported 1 records. Import failed for 2 records. \n", $out);
        }
        
        // check if users (with their data) have been added to tine20
        $user = Tinebase_User::getInstance()->getFullUserByLoginName($username);
        if ($username == 'hmoster') {
            $this->assertEquals('Hins', $user->accountFirstName);
        }
        $config = TestServer::getInstance()->getConfig();
        $maildomain = ($config->maildomain) ? $config->maildomain : 'tine20.org';
        $this->assertEquals($username . '@' . $maildomain, $user->accountEmailAddress);
    }

    /**
     * test to import admin users
     */
    public function testImportUsersWithHeadline()
    {
        $out = $this->_importUsers($this->objects['configWithHeadline'], dirname(__FILE__) . '/files/testHeadline.csv', 'admin_user_import_csv_test_headline');
        $this->_checkResult($out);
    }
    
    /**
     * testImportUsersWithEmailAndSemicolon
     * 
     * @see 0008300: Import User via CLI don't import all fields
     */
    public function testImportUsersWithEmailAndSemicolon()
    {
        $out = $this->_importUsers($this->objects['configSemicolon'], dirname(__FILE__) . '/files/tine_user3.csv', 'admin_user_import_csv_test_semicolon');
        $this->_checkResult($out, 'irmeli');
    }
    
    /**
     * testImportUsersWithEmailUser
     */
    public function testImportUsersWithEmailUser()
    {
        $userBackend = Tinebase_User::getInstance();
        $config = TestServer::getInstance()->getConfig();
        $maildomain = ($config->maildomain) ? $config->maildomain : 'tine20.org';

        $readFile = fopen(dirname(__FILE__) . '/files/tine_user5.csv', 'r');
        $writeFile = fopen('test.csv', 'w');
        $delimiter = ',';
        $enclosure = '"';
        
        while (($row = fgetcsv($readFile)) !== false) {
            foreach ($row as $colIndex => &$field) {
                $field = str_replace('DOMAIN', $maildomain, $field);
            }
            fputcsv($writeFile, $row, $delimiter, $enclosure);
        }
        
        fclose($readFile);
        fclose($writeFile);
        
        if (! array_key_exists('Tinebase_EmailUser_Smtp_Postfix', $userBackend->getPlugins())) {
            $this->markTestSkipped('Postfix SQL plugin not enabled');
        }
        
        $this->_importUsers($this->objects['configEmailuser'], 'test.csv', 'admin_user_import_csv_test_emailuser');
        $newUser = $userBackend->getFullUserByLoginName('testuser');
        $this->assertEquals(array('contact@' . $maildomain, 'kontakt@' . $maildomain), $newUser->smtpUser->emailAliases);
        $this->assertEquals(array('test@' . $maildomain), $newUser->smtpUser->emailForwards);
        $this->assertTrue($newUser->smtpUser->emailForwardOnly);
        unlink("test.csv");
    }
    
    /**
     * testImportUsersAdvanced
     */
    public function testImportUsersAdvanced()
    {
        $userBackend = Tinebase_User::getInstance();
        
        $readFile = fopen(dirname(__FILE__) . '/files/test_teacher.csv', 'r');
        $writeFile = fopen('test2.csv', 'w');
        $delimiter = ',';
        $enclosure = '"';
        
        while (($row = fgetcsv($readFile)) !== false) {
            foreach ($row as $colIndex => &$field) {
                $field = str_replace('PRIMARYGROUP', $this->_testGroup['domainuser']->getId(), $field);
                $field = str_replace('GROUP1', $this->_testGroup['teacher']->getId(), $field);
                $field = str_replace('GROUP2', $this->_testGroup['student']->getId(), $field);
            }
            fputcsv($writeFile, $row, $delimiter, $enclosure);
        }
        
        fclose($readFile);
        fclose($writeFile);
        
        $this->_importUsers($this->objects['configAdvanced'], 'test2.csv', 'admin_user_import_csv_test_advanced');
        $newUser = Tinebase_User::getInstance()->getFullUserByLoginName('m.muster');
        
        $newUserMemberships = Tinebase_Group::getInstance()->getGroupMemberships($newUser);
        $this->assertTrue(in_array($this->_testGroup['domainuser']->getId(), $newUserMemberships),
        ' not member of the domainuser group (' . $this->_testGroup['domainuser']->getId() . ') ' . print_r($newUserMemberships, TRUE));
        $this->assertTrue(in_array($this->_testGroup['student']->getId(), $newUserMemberships),
        ' not member of the student group (' . $this->_testGroup['student']->getId() . ') ' . print_r($newUserMemberships, TRUE));
        $this->assertTrue(in_array($this->_testGroup['teacher']->getId(), $newUserMemberships),
        ' not member of the teacher group (' . $this->_testGroup['teacher']->getId() . ') ' . print_r($newUserMemberships, TRUE));
        
        $this->assertEquals('/bin/false', $newUser->accountLoginShell);
        $this->assertEquals('/storage/lehrer/m.muster', $newUser->accountHomeDirectory);
        
        if (array_key_exists('Tinebase_User_Plugin_Samba', $userBackend->getPlugins())) {
            $this->assertEquals('\\fileserver\profiles\m.muster', $newUser->sambaSAM->profilePath);
        }
        unlink("test2.csv");
    }
    
    /**
     * tests if import with members from csv works correctly
     */
    public function testImportGroups()
    {
        $opts = new Zend_Console_Getopt('abp:');
        $opts->setArguments(array(dirname(__FILE__) . '/files/import_groups.csv', 'definition=admin_group_import_csv'));
        
        // start import (dry run)
        ob_start();
        $this->_cli->importGroups($opts);
        $out = ob_get_clean();
        
        $this->assertStringStartsWith('Imported 2 records.', $out);
        
        $be = new Tinebase_Group_Sql();
        $menGroup = $be->getGroupByName('men');
        $members = $be->getGroupMembers($menGroup);
        
        $this->assertEquals(3, count($members));
        
        $womenGroup = $be->getGroupByName('women');
        $members = $be->getGroupMembers($womenGroup);
        
        $this->assertEquals(2, count($members));
    }
}
