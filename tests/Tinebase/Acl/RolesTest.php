<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Tinebase
 * @subpackage  Acl
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @version     $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Tinebase_Acl_RolesTest::main');
}

/**
 * Test class for Tinebase_Acl_Roles
 */
class Tinebase_Acl_RolesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array test objects
     */
    protected $objects = array();
    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
		$suite  = new PHPUnit_Framework_TestSuite('Tinebase_Acl_RolesTest');
        PHPUnit_TextUI_TestRunner::run($suite);
	}

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->objects['application'] = Tinebase_Application::getInstance()->getApplicationByName('Crm');
        $this->objects['user'] = new Tinebase_Account_Model_FullAccount(array(
            'accountId'             => 10,
            'accountLoginName'      => 'tine20phpunit',
            'accountDisplayName'    => 'tine20phpunit',
            'accountStatus'         => 'enabled',
            'accountExpires'        => NULL,
            'accountPrimaryGroup'   => Tinebase_Group_Sql::getInstance()->getGroupByName('Users')->getId(),
            'accountLastName'       => 'Tine 2.0',
            'accountFirstName'      => 'PHPUnit',
            'accountEmailAddress'   => 'phpunit@metaways.de'
        )); 
        $this->objects['role'] = new Tinebase_Acl_Model_Role(array(
            'id'                    => 10,
            'name'                  => 'phpunitrole',
            'description'           => 'test role for phpunit',
        ));

        // add account for group / role member tests
        try {
            Tinebase_Account::getInstance()->getAccountById($this->objects['user']->accountId) ;
        } catch ( Exception $e ) {
            Tinebase_Account::getInstance()->addAccount (  $this->objects['user'] );
        }
        
        return;        
    }

    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        // remove account
        Tinebase_Account::getInstance()->deleteAccount (  $this->objects['user']->accountId );             
    }

    /**
     * try to add a role
     *
     */
    public function testCreateRole()
    {
        $role = Tinebase_Acl_Roles::getInstance()->createRole($this->objects['role']);
        
        $this->assertEquals($role->getId(), $this->objects['role']->getId());
    }    
    
    /**
     * try to add a role membership
     *
     */
    public function testSetRoleMember()
    {
        $member = array(
            array(
                "type"  => 'user',
                "id"    => $this->objects['user']->getId(),
            )
        );
        Tinebase_Acl_Roles::getInstance()->setRoleMembers($this->objects['role']->getId(), $member);
        
        $members = Tinebase_Acl_Roles::getInstance()->getRoleMembers($this->objects['role']->getId());
        
        $this->assertGreaterThan(0, count($members));
    }    
    
    /**
     * try to add a role right
     *
     */
    public function testSetRoleRight()
    {
        $right = array(
            array(
                "application_id"    => $this->objects['application']->getId(),
                "right"             => Tinebase_Acl_Rights::RUN,
            )
        );
        Tinebase_Acl_Roles::getInstance()->setRoleRights($this->objects['role']->getId(), $right);
        
        $rights = Tinebase_Acl_Roles::getInstance()->getRoleRights($this->objects['role']->getId());
        
        $this->assertGreaterThan(0, count($rights));
    }    
    
    /**
     * try to check if user with a role has right
     *
     */
    public function testHasRight()
    {
        $result = Tinebase_Acl_Roles::getInstance()->hasRight(
            $this->objects['application']->getId(), 
            $this->objects['user']->getId(), 
            Tinebase_Acl_Rights::RUN
        );
        
        $this->assertTrue($result);
        
        $result = Tinebase_Acl_Roles::getInstance()->hasRight(
            $this->objects['application']->getId(), 
            $this->objects['user']->getId(), 
            Tinebase_Acl_Rights::ADMIN
        );

        $this->assertFalse($result);
    }    

    /**
     * try to delete a role
     *
     */
    public function testDeleteRole()
    {
        // remove role members and rights first
        Tinebase_Acl_Roles::getInstance()->setRoleRights($this->objects['role']->getId(), array());
        Tinebase_Acl_Roles::getInstance()->setRoleMembers($this->objects['role']->getId(), array());        
        
        Tinebase_Acl_Roles::getInstance()->deleteRoles($this->objects['role']->getId());
                      
        $this->setExpectedException('Exception');
        
        Tinebase_Acl_Roles::getInstance()->getRoleById($this->objects['role']->getId());
    }    
    
}		
	

if (PHPUnit_MAIN_METHOD == 'Tinebase_Acl_RolesTest::main') {
    Tinebase_Acl_RolesTest::main();
}
