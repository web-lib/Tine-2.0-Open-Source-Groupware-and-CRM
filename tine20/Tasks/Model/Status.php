<?php
/**
 * eGroupWare 2.0
 * 
 * @package     Tasks
 * @license     http://www.gnu.org/licenses/agpl.html
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2007-2007 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 *
 */

/**
 * Task-Status Record Class
 * @package Tasks
 */
class Tasks_Model_Status extends Egwbase_Record_Abstract
{
    protected $_identifier = 'identifier';
    
    protected $_validators = array(
        'identifier'           => array('allowEmpty' => true,  'Int' ),
        'created_by'           => array('allowEmpty' => true,  'Int' ),
        'creation_time'        => array('allowEmpty' => true         ),
        'last_modified_by'     => array('allowEmpty' => true         ),
        'last_modified_time'   => array('allowEmpty' => true         ),
        'is_deleted'           => array('allowEmpty' => true         ),
        'deleted_time'         => array('allowEmpty' => true         ),
        'deleted_by'           => array('allowEmpty' => true         ),
        'status_name'          => array('allowEmpty' => false        ),
        'status_is_open'       => array('allowEmpty' => false        ),
        'status_icon'          => array('allowEmpty' => true         ),
    );
    
    protected $_datetimeFields = array(
        'creation_time',
        'last_modified_time',
        'deleted_time',
    );
}