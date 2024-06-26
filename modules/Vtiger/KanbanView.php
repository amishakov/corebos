<?php
/*************************************************************************************************
 * Copyright 2021 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO coreBOS Customizations.
* Licensed under the vtiger CRM Public License Version 1.1 (the "License"); you may not use this
* file except in compliance with the License. You can redistribute it and/or modify it
* under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
* granted by the License. coreBOS distributed by JPL TSolucio S.L. is distributed in
* the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
* applicable law or agreed to in writing, software distributed under the License is
* distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
* either express or implied. See the License for the specific language governing
* permissions and limitations under the License. You may obtain a copy of the License
* at <http://corebos.org/documentation/doku.php?id=en:devel:vpl11>
*************************************************************************************************/
$bmapname = $currentModule.'_Kanban';
if (isset($_REQUEST['bmapname'])) {
	$bmapname = vtlib_purify($_REQUEST['bmapname']);
}
$cbMapid = GlobalVariable::getVariable('BusinessMapping_'.$bmapname, cbMap::getMapIdByName($bmapname), $currentModule);
if ($cbMapid) {
	$cbMap = cbMap::getMapByID($cbMapid);
	$cbMapKb = $cbMap->Kanban();
}
if (empty($cbMapKb)) {
	$smarty->assign('showDesert', true);
} else {
	require_once 'include/utils/VtlibUtils.php';
	$smarty->assign('showDesert', false);
	$kanbanID = uniqid('kb'.strtolower($currentModule));
	$smarty->assign('kanbanID', $kanbanID);
	$cbMapKb['kanbanID'] = $kanbanID;
	$cbMapKb['currentPage'] = 0;
	$smarty->assign('kanbanBoardInfo', json_encode($cbMapKb));
	$smarty->assign('moduleShowSearch', $cbMapKb['showsearch']);
	$smarty->assign('moduleShowFilter', $cbMapKb['showfilter']);
	$smarty->assign('kbLanes', $cbMapKb['lanes']);
	$tabid = getTabid($currentModule);
	$customlink_params = array(
		'MODULE' => $currentModule,
		'RECORD' => '0x0',
		'ACTION' => vtlib_purify($_REQUEST['action'])
	);
	$linksurls = BusinessActions::getAllByType($tabid, array(
		'KANBANBUTTON',
		'KANBANHEADER'
	), $customlink_params);
	if (!empty($linksurls['KANBANBUTTON'])) {
		$smarty->assign('BALinks', $linksurls['KANBANBUTTON']);
	}
	if (!empty($linksurls['KANBANHEADER'])) {
		$HeaderAction = array();
		foreach ($linksurls['KANBANHEADER'] as $row) {
			$linklabel = explode('_', $row->linklabel);
			if (isset($linklabel[3])) {
				$HeaderAction[$linklabel[3]] = strip_tags(vtlib_process_widget($row));
			}
		}
		$smarty->assign('HeaderAction', $HeaderAction);
	}
	$smarty->assign('USERSWSID', vtws_getEntityId('Users'));
	$smarty->assign('lanefield', $cbMapKb['lanefield']);
}
$smarty->assign('moduleView', 'Kanban');
?>