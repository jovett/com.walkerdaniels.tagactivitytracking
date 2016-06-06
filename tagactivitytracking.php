<?php

require_once 'tagactivitytracking.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tagactivitytracking_civicrm_config(&$config) {
  _tagactivitytracking_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function tagactivitytracking_civicrm_xmlMenu(&$files) {
  _tagactivitytracking_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tagactivitytracking_civicrm_install() {
  $result = civicrm_api3('OptionGroup', 'get', array(
    'name' => "activity_type",
    'api.OptionValue.create' => array(
      'option_group_id' => "\$value.id",
      'label' => "Tag Added",
      'name' => "tag_added",
      'description' => 'Activity to record that a tag is being added',
      'is_reserved' => TRUE,
    ),
    'api.OptionValue.create.1' => array(
      'option_group_id' => "\$value.id",
      'label' => "Tag Removed",
      'name' => "tag_removed",
      'description' => 'Activity to record that a tag is being removed',
      'is_reserved' => TRUE,
    ),
  ));

  _tagactivitytracking_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function tagactivitytracking_civicrm_uninstall() {
  foreach (array('tag_added', 'tag_removed') as $name) {
    // Get all desired activities first
    $result = civicrm_api3('Activity', 'get', array('activity_type_id' => $name));

    // delete all desired activities
    foreach ($result['values'] as $id => $doNotCare) {
      civicrm_api3('Activity', 'delete', array('id' => $id));
    }

    // at last delete the activity type
    civicrm_api3('OptionValue', 'get', array(
      'name' => $name,
      'api.OptionValue.delete' => array(
        'id' => "\$value.id",
      ),
    ));
  }
  _tagactivitytracking_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tagactivitytracking_civicrm_enable() {
  civicrm_api3('OptionValue', 'get', array(
    'name' => "tag_added",
    'api.OptionValue.create' => array(
      'id' => "\$value.id",
      'is_active' => TRUE,
    ),
  ));
  civicrm_api3('OptionValue', 'get', array(
    'name' => "tag_removed",
    'api.OptionValue.create' => array(
      'id' => "\$value.id",
      'is_active' => TRUE,
    ),
  ));
  _tagactivitytracking_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function tagactivitytracking_civicrm_disable() {
  civicrm_api3('OptionValue', 'get', array(
    'sequential' => 1,
    'name' => "tag_added",
    'api.OptionValue.create' => array(
      'id' => "\$value.id",
      'is_active' => FALSE,
    ),
  ));
  civicrm_api3('OptionValue', 'get', array(
    'sequential' => 1,
    'name' => "tag_removed",
    'api.OptionValue.create' => array(
      'id' => "\$value.id",
      'is_active' => FALSE,
    ),
  ));
  _tagactivitytracking_civix_civicrm_disable();
}

function tagactivitytracking_civicrm_post($action, $entity, $entityID, $object) {
  if ($entity == 'EntityTag' && (CRM_Utils_Array::value(1, $object) == 'civicrm_contact')) {
    $tagResult = civicrm_api3('Tag', 'get', array('id' => $entityID));

    $validUser = CRM_Core_Session::getLoggedInContactID();

    // retrieve user id from api key when hit through REST url
    if ($apiKey = CRM_Utils_Request::retrieve('api_key', 'String')) {
      $validUser = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $apiKey, 'id', 'api_key');
    }

    switch($action) {
      case 'create':
        civicrm_api3('Activity', 'create', array(
          'activity_type_id' => "tag_added",
          'target_id' => $object[0][0],
          'subject' => $tagResult['values'][$entityID]['name'],
          'source_record_id' => $entityID,
          'source_contact_id' => $validUser,
        ));
      break;

      case 'delete':
        civicrm_api3('Activity', 'create', array(
          'activity_type_id' => "tag_removed",
          'target_id' => $object[0][0],
          'subject' => $tagResult['values'][$entityID]['name'],
          'source_record_id' => $entityID,
          'source_contact_id' => $validUser,
        ));
        break;
    }
  }
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tagactivitytracking_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _tagactivitytracking_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function tagactivitytracking_civicrm_managed(&$entities) {
  _tagactivitytracking_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tagactivitytracking_civicrm_caseTypes(&$caseTypes) {
  _tagactivitytracking_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function tagactivitytracking_civicrm_angularModules(&$angularModules) {
_tagactivitytracking_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function tagactivitytracking_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _tagactivitytracking_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function tagactivitytracking_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function tagactivitytracking_civicrm_navigationMenu(&$menu) {
  _tagactivitytracking_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'com.mobilepocketoffice.tagactivitytracking')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _tagactivitytracking_civix_navigationMenu($menu);
} // */
