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
<!-- BEGIN: main -->
<div id="roleLay" style="z-index:12;display:inline-table;width:400px; margin-top: 40px;" class="layerPopup slds-card small">
	<input name="excludedRecords" type="hidden" id="excludedRecords" value="{$EXE_REC}">
	<input name='search_url' id="search_url" type='hidden' value='{$SEARCH_URL}'>
	<input name='viewid' id="viewid" type='hidden' value='{$VIEWID}'>
	<input name='recordid' id="recordid" type='hidden' value='{$RECORDID}'>
	<table border=0 cellspacing=0 cellpadding=5 width=100% class="layerHeadingULine slds-table slds-table_bordered">
		<tr>
			<td width="90%" align="left" class="genHeaderSmall">{$MOD.SELECT_EMAIL}
				{if $ONE_RECORD neq 'true'}
				({$MOD.LBL_MULTIPLE} {$FROM_MODULE|getTranslatedString:$FROM_MODULE})
				{/if}
				&nbsp;
			</td>
			<td width="10%" align="right">
				<a href="javascript:fninvsh('roleLay');"><img title="{$APP.LBL_CLOSE}" alt="{$APP.LBL_CLOSE}" src="{'close.gif'|@vtiger_imageurl:$THEME}" border="0"  align="absmiddle" /></a>
			</td>
		</tr>
	</table>
	<table border=0 cellspacing=0 cellpadding=5 width=95% align=center style="padding: 7px;">
		<tr><td class="small">
			<table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
				<tr>
					<td align="left">
					{if $ONE_RECORD eq 'true'}
						<b>{$ENTITY_NAME}</b> {$MOD.LBL_MAILSELECT_INFO}.<br><br>
					{else}
						{$MOD.LBL_MAILSELECT_INFO1} {$FROM_MODULE|getTranslatedString:$FROM_MODULE}.{$MOD.LBL_MAILSELECT_INFO2}<br><br>
					{/if}
						<div style="height:120px;overflow-y:auto;overflow-x:hidden;" align="center">
							<table border="0" cellpadding="5" cellspacing="0" width="90%">
								{foreach name=emailids key=fieldid item=elements from=$MAILINFO}
								<tr>
									{if $smarty.foreach.emailids.iteration eq 1}
										<td align="center">
											<div class="slds-form-element__control">
												<div class="slds-checkbox">
													<input type="checkbox" name="semail" id="{$fieldid}" value="{$fieldid}" checked="" />
													<label class="slds-checkbox__label" for="{$fieldid}">
														<span class="slds-checkbox_faux"></span>
													</label>
												</div>
											</div>
										</td>
									{else}
									<td align="center">
										<div class="slds-form-element__control">
											<div class="slds-checkbox">
												<input type="checkbox" name="semail" id="{$fieldid}" value="{$fieldid}" />
												<label class="slds-checkbox__label" for="{$fieldid}">
													<span class="slds-checkbox_faux"></span>
												</label>
											</div>
										</div>
									</td>
									{/if}
									{if $PERMIT eq '0'}
									{if $ONE_RECORD eq 'true'}
									<td align="left"><b>{$elements.0}</b><br>{$MAILDATA[$smarty.foreach.emailids.index]}</td>
									{else}
									<td align="left"><b>{$elements.0}</b></td>
									{/if}
									{else}
									<td align="left"><b>{$elements.0}</b><br>{$MAILDATA[$smarty.foreach.emailids.index]}</td>
									{/if}
								</tr>
								{/foreach}
							</table>
						</div>
					</td>
				</tr>
			</table>
		</td></tr>
	</table>
	<table style="text-align: center; margin: auto; display: flex; justify-content: center; display: grid;" border=0 cellspacing=0 cellpadding=5 width=100% class="layerPopupTransport slds-table slds-table_bordered">
		<tr><td align=center class="small">
			<input type="button" name="{$APP.LBL_SELECT_BUTTON_LABEL}" value=" {$APP.LBL_SELECT_BUTTON_LABEL} " class="slds-button slds-button_brand small crmbutton create" onClick="validate_sendmail('{$IDLIST}','{$FROM_MODULE}');"/>&nbsp;&nbsp;
			<input type="button" name="{$APP.LBL_CANCEL_BUTTON_LABEL}" value=" {$APP.LBL_CANCEL_BUTTON_LABEL} " class="slds-button slds-button_destructive small crmbutton cancel" onclick="fninvsh('roleLay');" />
		</td></tr>
	</table>
</div>
