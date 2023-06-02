<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
require_once 'include/logging.php';
require_once 'modules/Users/Users.php';
require_once 'include/database/PearDatabase.php';
require_once 'Smarty_setup.php';
global $adb ,$mod_strings, $current_user, $currentModule, $app_strings, $theme;

$smarty = new vtigerCRM_Smarty();
$local_log = LoggerManager::getLogger('UsersAjax');
$ajaxaction = vtlib_purify($_REQUEST['ajxaction']);
if ($ajaxaction == 'DETAILVIEW') {
	$userid = vtlib_purify($_REQUEST['recordid']);
	$fieldname = vtlib_purify($_REQUEST['fldName']);
	$fieldvalue = utf8RawUrlDecode(vtlib_purify($_REQUEST['fieldValue']));
	if (empty($_SESSION['Users_FORM_TOKEN']) || $_SESSION['Users_FORM_TOKEN'] !== (int)$_REQUEST['form_token'] ||
		(!is_admin($current_user) && $current_user->id != $userid)) {
		echo ':#:ERR'.($app_strings['LBL_PERMISSION']);
		die;
	}
	if ($userid != '') {
		$userObj = new Users();
		$userObj->retrieve_entity_info($userid, 'Users');
		$userObj->column_fields[$fieldname] = $fieldvalue;

		if ($fieldname=='asterisk_extension' && trim($fieldvalue)!='') {
			$query = "select userid from vtiger_asteriskextensions
				inner join vtiger_users on vtiger_users.id=vtiger_asteriskextensions.userid
				where status='Active' and asterisk_extension =? and vtiger_users.id!=?";
			$params = array(trim($fieldvalue),$userid);
			$result = $adb->pquery($query, $params);
			if ($adb->num_rows($result) > 0) {
				echo ':#:ERR'.$mod_strings['LBL_ASTERISKEXTENSIONS_EXIST'].$mod_strings['LBL_FORUSER'].getUserFullName($result->fields['userid']);
				return false;
			}
		}
		if (($fieldname=='currency_grouping_separator' || $fieldname=='currency_decimal_separator')
			&& $userObj->column_fields['currency_grouping_separator']==$userObj->column_fields['currency_decimal_separator']
		) {
			echo ':#:ERR'.$mod_strings['LBL_CURRENCY_SEPARATORS_INCORRECT'];
			return false;
		}
		if ($fieldname == 'internal_mailer' && isset($_SESSION['internal_mailer']) && $_SESSION['internal_mailer'] != $userObj->column_fields['internal_mailer']) {
			coreBOS_Session::set('internal_mailer', $userObj->column_fields['internal_mailer']);
		}
		$userObj->id = $userid;
		$userObj->mode = 'edit';
		$userObj->homeorder_array[] = 'Tag Cloud';
		$homeStuffOrder = $userObj->getHomeStuffOrder($userid);
		foreach ($homeStuffOrder as $widget => $visible) {
			$_REQUEST[$widget] = $visible;
		}
		$_REQUEST['tagcloudview'] = $homeStuffOrder['Tag Cloud'];
		$userObj->column_fields['first_name'] = vtlib_purify($userObj->column_fields['first_name']);
		$userObj->column_fields['last_name'] = vtlib_purify($userObj->column_fields['last_name']);
		$userObj->column_fields['email1'] = filter_var($userObj->column_fields['email1'], FILTER_SANITIZE_EMAIL);
		$userObj->column_fields['email2'] = filter_var($userObj->column_fields['email2'], FILTER_SANITIZE_EMAIL);
		$userObj->column_fields['secondaryemail'] = filter_var($userObj->column_fields['secondaryemail'], FILTER_SANITIZE_EMAIL);
		$userObj->save('Users');
		if ($userObj->id != '') {
			echo ':#:SUCCESS:#:';
			$_REQUEST['action'] = $currentModule;
			decide_to_html();
			require_once 'modules/'.$currentModule.'/DetailView.php';
			$_REQUEST['action'] = $currentModule.'Ajax';
			decide_to_html();
		} else {
			echo ':#:FAILURE';
		}
	} else {
		echo ':#:FAILURE';
	}
}
?>