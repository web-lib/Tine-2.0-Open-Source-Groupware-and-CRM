<?php
/**
 * Tine 2.0
 * 
 * @package     Tinebase
 * @subpackage  Group
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id$
 */

/**
 * Group factory class
 * 
 * this class is responsible for returning the right group backend
 *
 * @package     Tinebase
 * @subpackage  Group
 */
class Tinebase_Group_Factory
{
    const SQL = 'Sql';
    
    const LDAP = 'Ldap';
    
    /**
     * return an instance of the current accounts backend
     *
     * @return Tinebase_Group_Interface
     */
    public static function getBackend($_backendType) 
    {
        switch($_backendType) {
            case self::LDAP:
                $result = Tinebase_Group_Ldap::getInstance();
                break;
                
            case self::SQL:
                $result = Tinebase_Group_Sql::getInstance();
                break;
            
            default:
                throw new Exception("accounts backend type $_backendType not implemented");
        }
        
        return $result;
    }
}