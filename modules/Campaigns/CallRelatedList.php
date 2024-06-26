<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'Smarty_setup.php';
require 'modules/Vtiger/default_module_view.php';
require_once 'modules/CustomView/CustomView.php';
global $mod_strings, $app_strings, $currentModule, $current_user, $theme, $log;

$action = vtlib_purify($_REQUEST['action']);
$record = vtlib_purify($_REQUEST['record']);
$isduplicate = isset($_REQUEST['isDuplicate']) ? vtlib_purify($_REQUEST['isDuplicate']) : false;

if ($singlepane_view == 'true' && $action == 'CallRelatedList') {
	echo "<script>document.location='index.php?action=DetailView&module=".urlencode($currentModule).'&record='.urlencode($record)."';</script>";
	die();
} else {
	$tool_buttons = Button_Check($currentModule);

	$focus = CRMEntity::getInstance($currentModule);
	if ($record != '') {
		$focus->retrieve_entity_info($record, $currentModule);
		$focus->id = $record;
	}

	$smarty = new vtigerCRM_Smarty;
	if (strpos($focus->moduleIcon['icon'], '-') !== false) {
		$iconArray = explode('-', $focus->moduleIcon['icon']);
		$focus->moduleIcon['icon'] = end($iconArray);
	}
	$smarty->assign('currentModuleIcon', $focus->moduleIcon);

	if ($isduplicate == 'true') {
		$focus->id = '';
	}
	if (isset($_REQUEST['mode']) && $_REQUEST['mode'] != ' ') {
		$smarty->assign('OP_MODE', vtlib_purify($_REQUEST['mode']));
	}
	if (empty($_SESSION['rlvs'][$currentModule])) {
		coreBOS_Session::delete('rlvs');
	}

	// Identify this module as custom module.
	$smarty->assign('CUSTOM_MODULE', $focus->IsCustomModule);
	$errormessageclass = isset($_REQUEST['error_msgclass']) ? vtlib_purify($_REQUEST['error_msgclass']) : '';
	$errormessage = isset($_REQUEST['error_msg']) ? vtlib_purify($_REQUEST['error_msg']) : '';
	$smarty->assign('ERROR_MESSAGE_CLASS', $errormessageclass);
	$smarty->assign('ERROR_MESSAGE', $errormessage);

	$smarty->assign('APP', $app_strings);
	$smarty->assign('MOD', $mod_strings);
	$smarty->assign('MODULE', $currentModule);
	$smarty->assign('SINGLE_MOD', getTranslatedString('SINGLE_'.$currentModule, $currentModule));
	$smarty->assign('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign('THEME', $theme);
	$smarty->assign('ID', $focus->id);
	$smarty->assign('MODE', $focus->mode);
	$smarty->assign('CHECK', $tool_buttons);
	$smarty->assign('RECORDID', $record);
	$smarty->assign('MAX_RECORDS', GlobalVariable::getVariable('Application_ListView_PageSize', 20, $currentModule));

	$smarty->assign('NAME', $focus->column_fields[$focus->def_detailview_recname]);
	$smarty->assign('UPDATEINFO', updateInfo($focus->id));

	// Module Sequence Numbering
	$mod_seq_field = getModuleSequenceField($currentModule);
	if ($mod_seq_field != null) {
		$mod_seq_id = $focus->column_fields[$mod_seq_field['name']];
	} else {
		$mod_seq_id = $focus->id;
	}
	$smarty->assign('MOD_SEQ_ID', $mod_seq_id);
	$smarty->assign('HASRELATEDPANES', 'false');
	$restrictedRelations = null;
	$related_array = getRelatedLists($currentModule, $focus, $restrictedRelations);
	// vtlib customization: Related module could be disabled, check it
	if (isset($related_array)) {
		foreach ($related_array as $mod_key => $mod_val) {
			if ($mod_key == 'Contacts' || $mod_key == 'Leads') {
				$rel_checked=isset($_REQUEST[$mod_key.'_all']) ? $_REQUEST[$mod_key.'_all'] : '';
				$rel_check_split=explode(';', $rel_checked);
				if (is_array($mod_val)) {
					$mod_val['checked']=array();
					if (isset($mod_val['entries'])) {
						foreach ($mod_val['entries'] as $key => $val) {
							if (in_array($key, $rel_check_split)) {
								$related_array[$mod_key]['checked'][$key] = 'checked';
							} else {
								$related_array[$mod_key]['checked'][$key] = '';
							}
						}
					}
				}
			}
		}
	}
	$smarty->assign('RELATEDLISTS', $related_array);

	require_once 'include/ListView/RelatedListViewSession.php';
	if (!empty($_REQUEST['selected_header']) && !empty($_REQUEST['relation_id'])) {
		$relationId = vtlib_purify($_REQUEST['relation_id']);
		RelatedListViewSession::addRelatedModuleToSession($relationId, vtlib_purify($_REQUEST['selected_header']));
	}
	$open_related_modules = RelatedListViewSession::getRelatedModulesFromSession();
	$smarty->assign('SELECTEDHEADERS', $open_related_modules);
	// Gather the custom link information to display
	include_once 'vtlib/Vtiger/Link.php';
	$customlink_params = array('MODULE'=>$currentModule, 'RECORD'=>$focus->id, 'ACTION'=>vtlib_purify($_REQUEST['action']));
	$smarty->assign(
		'CUSTOM_LINKS',
		Vtiger_Link::getAllByType(getTabid($currentModule), array('DETAILVIEWBUTTON','DETAILVIEWBUTTONMENU'), $customlink_params, null, $focus->id)
	);
	$smarty->assign('TABSCOPED', empty(GlobalVariable::getVariable('Application_RelatedPane_Scoped', '1')) ? 'default' : 'scoped');

	if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '') {
		$smarty->display('RelatedListContents.tpl');
	} else {
		$smarty->display('RelatedLists.tpl');
	}
}
?>
