{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
 ********************************************************************************/
-->*}
{include file='SetMenu.tpl'}
<section role="dialog" tabindex="-1" class="slds-fade-in-open slds-modal_large slds-app-launcher" aria-labelledby="header43">
<div class="slds-modal__container slds-p-around_none">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
<tbody><tr>
	<td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
	<br>

	<div align=center>
				<!-- DISPLAY -->
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
				<form id="form" name="roleView" action="index.php" method="post" onsubmit="VtigerJS_DialogBox.block();">
				<input type="hidden" name="module" value="Settings">
				<input type="hidden" name="action" value="createrole">
				<input type="hidden" name="returnaction" value="RoleDetailView">
				<input type="hidden" name="roleid" value="{$ROLEID}">
				<input type="hidden" name="mode" value="edit">
				<tr>
					<td width=50 rowspan=2 valign=top class="cblds-p_none"><img src="{'ico-roles.gif'|@vtiger_imageurl:$THEME}" width="48" height="48" border=0 ></td>
					<td class=heading2 valign=bottom><b><a href="index.php?module=Settings&action=index">{'LBL_SETTINGS'|@getTranslatedString}</a> > <a href="index.php?module=Settings&action=listroles">{$CMOD.LBL_ROLES}</a> &gt; {$CMOD.LBL_VIEWING} &quot;{$ROLE_NAME}&quot; </b></td>
				</tr>
				<tr>
					<td valign=top class="small cblds-p-v_none">{$CMOD.LBL_VIEWING} {$CMOD.LBL_PROPERTIES} &quot;{$ROLE_NAME}&quot; {$MOD.LBL_LIST_CONTACT_ROLE} </td>
				</tr>
				</table>
				<br>
				<table border=0 cellspacing=0 cellpadding=10 width=100% >
				<tr>
				<td valign=top>
					<table border=0 cellspacing=0 cellpadding=5 width=100% class="tableHeading">
					<tr>
						<td class="big"><strong>{$CMOD.LBL_PROPERTIES} &quot;{$ROLE_NAME}&quot; </strong></td>
						<td><div align="right">
							<input value=" {$APP.LBL_EDIT_BUTTON_LABEL} " title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="crmButton small edit" type="submit" name="Edit" >
							<input type="button" onclick="VtigerJS_DialogBox.block();window.document.location.href = 'index.php?module=Settings&action=SettingsAjax&file=CalculatePrivilegeFilesRole&roleid={$ROLEID}';" value=" {$APP.LBL_RECALCULATE_BUTTON} " class="crmButton small cancel">
						</div></td>
					</tr>
					</table>
					<table width="100%" border="0" cellspacing="0" cellpadding="5">
						<tr class="small">
							<td width="15%" class="small cellLabel"><strong>{$CMOD.LBL_ROLE_NAME}</strong></td>
							<td width="85%" class="cellText" >{$ROLE_NAME}</td>
						</tr>
						<tr class="small">
							<td class="small cellLabel"><strong>{$CMOD.LBL_REPORTS_TO}</strong></td>
							<td class="cellText">{$PARENTNAME}</td>
						</tr>
						<tr class="small">
							<td valign=top class="cellLabel"><strong>{$CMOD.LBL_MEMBER}</strong></td>
							<td class="cellText">
							<table width="70%" border="0" cellspacing="0" cellpadding="5">
								<tr class="small">
									<td colspan="2" class="cellBottomDotLine">
										<div align="left"><strong>{$CMOD.LBL_ASSOCIATED_PROFILES}</strong></div>
									</td>
								</tr>
						{foreach item=elements from=$ROLEINFO.profileinfo}
						<tr class="small">
							<td width="16"><div align="center"></div></td>
							<td>
								<a href="index.php?module=Settings&action=profilePrivileges&profileid={$elements.0}&mode=view">{$elements.1}</a><br>
							</td>
						</tr>
						{/foreach}
						<tr class="small">
							<td colspan="2" class="cellBottomDotLine">
								<div align="left"><strong>{$CMOD.LBL_ASSOCIATED_USERS}</strong></div>
							</td>
						</tr>
					{if !empty($ROLEINFO.userinfo.0)}
						{foreach item=elements from=$ROLEINFO.userinfo}
						<tr class="small">
							<td width="16"><div align="center"></div></td>
							<td>
								<a href="index.php?module=Users&action=DetailView&record={$elements.0}">{$elements.1}</a><br>
							</td>
						</tr>
						{/foreach}
					{/if}
					</table></td>
					</tr>
					</table>
				</td>
				</tr>
				</table>
			</td>
			</tr>
			</table>
		</td>
	</tr>
	</form>
	</table>
	</div>
</td>
	</tr>
</tbody>
</table>
</div>
</section>