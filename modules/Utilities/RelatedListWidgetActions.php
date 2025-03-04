<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  coreBOS Open Source
 * The Initial Developer of the Original Code is coreBOS.
 * Portions created by vtiger are Copyright (C) coreBOS.
 * All Rights Reserved.
 ********************************************************************************/
include_once 'include/ListView/GridUtils.php';
global $adb;
switch ($_REQUEST['rlaction']) {
	case 'delete':
		$rs = gridUnrelate($adb, $_REQUEST);
		echo json_encode($rs);
		break;
	case 'PopupAction':
		$data = json_decode($_REQUEST['data'], true);
		$res = true;
		if (!empty($data)) {
			if (!empty($data['relatedfield']) && !empty($data['relatedmodule'])) {
				$focus = CRMEntity::getInstance($data['module']);
				$focus->retrieve_entity_info($data['recordid'], $data['module']);
				$relatedvalue = $focus->column_fields[$data['relatedfield']];
				if (!empty($relatedvalue)) {
					$relfocus = CRMEntity::getInstance($data['relatedmodule']);
					if ($relfocus->isDeleted($relatedvalue)) {
						echo json_encode(false);
						exit;
					}
					$relfocus->retrieve_entity_info($relatedvalue, $data['relatedmodule']);
					$values = array_values($data['values']);
					$res = false;
					foreach ($values as $key) {
						if ($key['value'] == $relfocus->column_fields[$data['fieldname']]) {
							$res = (int)$key['id'];
							break;
						}
					}
				}
			}
		}
		echo json_encode($res);
		break;
	case 'CreateView':
		$data = json_decode($_REQUEST['data'], true);
		$res = true;
		if (!empty($data)) {
			if (!empty($data['relatedfield']) && !empty($data['relatedmodule'])) {
				$focus = CRMEntity::getInstance($data['module']);
				$setype = getSalesEntityType($data['recordid']);
				if ($setype != $data['module']) {
					echo json_encode(false);
					exit;
				}
				$focus->retrieve_entity_info($data['recordid'], $data['module']);
				$relatedvalue = $focus->column_fields[$data['relatedfield']];
				if (!empty($relatedvalue)) {
					$relfocus = CRMEntity::getInstance($data['relatedmodule']);
					if ($relfocus->isDeleted($relatedvalue)) {
						echo json_encode(false);
						exit;
					}
					$relfocus->retrieve_entity_info($relatedvalue, $data['relatedmodule']);
					if (is_string($data['values'])) {
						$data['values'] = (array)$data['values'];
					}
					$values = array_values($data['values']);
					$res = false;
					foreach ($values as $key) {
						if ($key == $relfocus->column_fields[$data['fieldname']]) {
							$res = true;
							break;
						}
					}
				}
			}
		}
		echo json_encode($res);
		break;
	case 'Wizard':
		$cbMap = cbMap::getMapByID($_REQUEST['mapid']);
		if (!empty($cbMap)) {
			$fields = $cbMap->column_fields;
			$_REQUEST['bmapname'] = $fields['mapname'];
		}
		require_once 'modules/Vtiger/WizardView.php';
		$smarty->assign('isWigdet', true);
		echo $smarty->fetch('Smarty/templates/WizardView.tpl');
		break;
	case 'list':
	default:
		if (empty($_REQUEST['mapname'])) {
			echo getEmptyDataGridResponse();
		} else {
			$mname = vtlib_purify($_REQUEST['mapname']);
			$cbMapid = GlobalVariable::getVariable('BusinessMapping_'.$mname, cbMap::getMapIdByName($mname));
			if ($cbMapid) {
				$cbMap = cbMap::getMapByID($cbMapid);
				$map = $cbMap->RelatedListBlock();
				$mods = end($map['modules']);
				if (!empty($mods['listview']) && !empty($_REQUEST['pid'])) {
					echo getRelatedListGridResponse($map);
				} else {
					echo getEmptyDataGridResponse();
				}
			} else {
				echo getEmptyDataGridResponse();
			}
		}
		break;
}
