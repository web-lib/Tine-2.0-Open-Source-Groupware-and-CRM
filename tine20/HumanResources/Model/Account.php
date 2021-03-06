<?php
/**
 * Tine 2.0

 * @package     HumanResources
 * @subpackage  Model
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Alexander Stintzing <a.stintzing@metaways.de>
 * @copyright   Copyright (c) 2013 Metaways Infosystems GmbH (http://www.metaways.de)
 */

/**
 * class to hold Account data
 *
 * @package     HumanResources
 * @subpackage  Model
 */

class HumanResources_Model_Account extends Tinebase_Record_Abstract
{
    /**
     * holds the configuration object (must be declared in the concrete class)
     *
     * @var Tinebase_ModelConfiguration
     */
    protected static $_configurationObject = NULL;
    
    /**
     * Holds the model configuration (must be assigned in the concrete class)
     *
     * @var array
     */
    protected static $_modelConfiguration = array(
        'recordName'        => 'Personal account', // ngettext('Personal account', 'Personal accounts', n)
        'recordsName'       => 'Personal accounts',
        'hasRelations'      => TRUE,
        'hasCustomFields'   => TRUE,
        'hasNotes'          => TRUE,
        'hasTags'           => TRUE,
        'modlogActive'      => TRUE,
        'hasAttachments'    => TRUE,
        
        'createModule'      => TRUE,
        'containerProperty' => NULL,
        
        'titleProperty'     => 'year',
        'appName'           => 'HumanResources',
        'modelName'         => 'Account',
        
        'filterModel' => array(
            'query' => array(
                'label' => 'Quick search',    // _('Quick search')
                'filter' => 'HumanResources_Model_AccountQuicksearchFilter',
                'jsConfig' => array('valueType' => 'string')
            )
        ),
        
        'fields'            => array(
            'employee_id' => array(
                'label' => 'Employee',
                'type'  => 'record',
                'validators' => array(Zend_Filter_Input::ALLOW_EMPTY => TRUE, Zend_Filter_Input::DEFAULT_VALUE => NULL),
                'duplicateCheckGroup' => 'year-employee',
                'config' => array(
                    'appName'     => 'HumanResources',
                    'modelName'   => 'Employee',
                    'idProperty'  => 'id'
                )
            ),
            'year' => array(
                'label' => 'Year', //_('Year')
                'duplicateCheckGroup' => 'year-employee',
                'group' => 'Account',
                'type'    => 'integer'
            ),
            'extra_free_times' => array(
                'validators' => array(Zend_Filter_Input::ALLOW_EMPTY => TRUE, Zend_Filter_Input::DEFAULT_VALUE => NULL),
                'label'      => 'Extra free times', // _('Extra free times')
                'type'       => 'records',
                'config'     => array(
                    'appName'     => 'HumanResources',
                    'modelName'   => 'ExtraFreeTime',
                    'refIdField'  => 'account_id',
                    'paging'      => array('sort' => 'creation_time', 'dir' => 'ASC'),
                    'dependentRecords' => TRUE
                ),
            ),
            'description' => array(
                'label' => 'Description', //_('Description')
                'group' => 'Miscellaneous', //_('Miscellaneous')
            ),
            // virtual fields
            'remaining_vacation_days' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'taken_vacation_days' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'possible_vacation_days' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'expired_vacation_days' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'excused_sickness' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'rebooked_vacation_days' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'unexcused_sickness' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'working_days' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'working_hours' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'working_days_real' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
            'working_hours_real' => array(
                'type' => 'virtual',
                'config' => array(
                    'type' => 'integer'
                )
            ),
        )
    );
}
