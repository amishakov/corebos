<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class DataTransform {

	public static $recordString = 'record_id';
	public static $recordModuleString = 'record_module';
	public static $donottrimuitype = array(Field_Metadata::UITYPE_FULL_WIDTH_TEXT_AREA, Field_Metadata::UITYPE_HALF_WIDTH_TEXT_AREA);
	public static $donottrimftype = array('currency', 'double', 'integer', 'date', 'time');

	public static function sanitizeDataWithColumn($row, $meta) {
		$newRow = array();
		if (isset($row['count(*)'])) {
			return DataTransform::sanitizeDataWithCountColumn($row, $meta);
		}
		$fieldColumnMapping = $meta->getFieldColumnMapping();
		$columnFieldMapping = array_flip($fieldColumnMapping);
		foreach ($row as $col => $val) {
			if (array_key_exists($col, $columnFieldMapping)) {
				$newRow[$columnFieldMapping[$col]] = $val;
			} else {
				$newRow[$col] = $val;
			}
		}
		$newRow = DataTransform::sanitizeData($newRow, $meta, true);
		return $newRow;
	}

	public static function sanitizeDataWithCountColumn($row, $meta) {
		$newRow = array();
		foreach ($row as $val) {
			$newRow['count'] = $val;
		}
		return $newRow;
	}

	public static function filterAndSanitize($row, $meta) {
		$row = DataTransform::filterAllColumns($row, $meta);
		$row = DataTransform::sanitizeData($row, $meta);
		return $row;
	}

	public static function sanitizeData($newRow, $meta, $t = null) {
		$newRow = DataTransform::sanitizeReferences($newRow, $meta);
		$newRow = DataTransform::sanitizeOwnerFields($newRow, $meta, $t);
		$newRow = DataTransform::sanitizeFields($newRow, $meta);
		return $newRow;
	}

	public static function sanitizeRetrieveEntityInfo($newRow, $meta, $sanitizeText = true) {
		$newRow = DataTransform::sanitizeDateFieldsForInsert($newRow, $meta);
		$newRow = DataTransform::sanitizeCurrencyFieldsForInsert($newRow, $meta);
		if ($sanitizeText) {
			$newRow = DataTransform::sanitizeTextFieldsForInsert($newRow, $meta);
		}
		return $newRow;
	}

	public static function sanitizeForInsert($row, $meta) {
		global $adb;
		$associatedToUser = false;
		$parentTypeId = null;
		if (strtolower($meta->getEntityName()) == 'emails' && isset($row['parent_id'])) {
			$components = vtws_getIdComponents($row['parent_id']);
			$userObj = VtigerWebserviceObject::fromName($adb, 'Users');
			$parentTypeId = $components[0];
			if ($components[0] == $userObj->getEntityId()) {
				$associatedToUser = true;
			}
		}
		// added to handle the setting reminder time
		if (strtolower($meta->getEntityName()) == 'events') {
			if (isset($row['reminder_time'])&& $row['reminder_time']!= null && $row['reminder_time'] != 0) {
				$_REQUEST['set_reminder'] = 'Yes';
				$_REQUEST['mode'] = 'edit';

				$reminder = $row['reminder_time'];
				$minutes = (int)($reminder/60)%60;
				$hours = (int)($reminder/(60*60))%24;
				$days = (int)($reminder/(60*60*24));

				// there cannot be 0 minutes reminder so we are setting to 1
				if ($minutes == 0) {
					$minutes = 1;
				}

				$_REQUEST['remmin'] = $minutes;
				$_REQUEST['remhrs'] = $hours;
				$_REQUEST['remdays'] = $days;
			} else {
				$_REQUEST['set_reminder'] = 'No';
			}
			if (isset($row['contact_id']) && strpos($row['contact_id'], ';')!==false) {
				$ctowsids = array();
				$listofctos = explode(';', $row['contact_id']);
				foreach ($listofctos as $cto) {
					if (strpos($cto, 'x')!==false) {
						$ctowsid = vtws_getIdComponents($cto);
						$ctowsids[] = $ctowsid[1];
					} else {
						$ctowsids[] = $cto;
					}
				}
				$row['contact_id'] = implode(';', $ctowsids);
			}
		}
		$references = $meta->getReferenceFieldDetails();
		foreach ($references as $field => $typeList) {
			if (isset($row[$field])) {
				if (strlen($row[$field])==40) {
					$row[$field] = CRMEntity::getCRMIDfromUUID($row[$field]);
				} elseif (strpos($row[$field], 'x')!==false) {
					list($void, $row[$field]) = vtws_getIdComponents($row[$field]);
				}
			}
		}
		$ownerFields = $meta->getOwnerFields();
		foreach ($ownerFields as $field) {
			if (isset($row[$field]) && $row[$field]!=null) {
				if (strpos($row[$field], 'x')!==false) {
					$ownerDetails = vtws_getIdComponents($row[$field]);
				} else {
					$ownerDetails[1] = $row[$field];
				}
				$row[$field] = $ownerDetails[1];
			}
		}
		if (strtolower($meta->getEntityName()) == 'emails' && isset($row['parent_id'])) {
			if ($associatedToUser === true) {
				$_REQUEST['module'] = 'Emails';
				$row['parent_id'] = $row['parent_id'].'@-1|';
				$_REQUEST['parent_id'] = $row['parent_id'];
			} else {
				$referenceHandler = vtws_getModuleHandlerFromId($parentTypeId, $meta->getUser());
				$referenceMeta = $referenceHandler->getMeta();
				$fieldId = getEmailFieldId($referenceMeta, $row['parent_id']);
				$row['parent_id'] .= "@$fieldId|";
			}
		}
		$row = DataTransform::trimTextFields($row, $meta);
		if (isset($row['id'])) {
			unset($row['id']);
		}
		if (isset($row[$meta->getObectIndexColumn()])) {
			unset($row[$meta->getObectIndexColumn()]);
		}
		return $row;
	}

	public static function trimTextFields($row, $meta) {
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			$ftype = $fieldObj->getFieldDataType();
			$uitype = $fieldObj->getUIType();
			if (!empty($row[$fieldName]) && !in_array($ftype, self::$donottrimftype) && !in_array($uitype, self::$donottrimuitype)) {
				$row[$fieldName] = trim($row[$fieldName]);
			}
		}
		return $row;
	}

	public static function filterAllColumns($row, $meta) {
		$recordString = DataTransform::$recordString;

		$allFields = $meta->getFieldColumnMapping();
		$newRow = array();
		foreach ($allFields as $field => $col) {
			$newRow[$field] = isset($row[$field]) ? $row[$field] : (isset($row[$col]) ? $row[$col] : '');
		}
		if (isset($row[$recordString])) {
			$newRow[$recordString] = $row[$recordString];
		}
		return $newRow;
	}

	public static function sanitizeFields($row, $meta) {
		$default_charset = VTWS_PreserveGlobal::getGlobal('default_charset');
		$recordString = DataTransform::$recordString;

		$recordModuleString = DataTransform::$recordModuleString;

		if (isset($row[$recordModuleString])) {
			unset($row[$recordModuleString]);
		}

		if (isset($row['id']) && strpos($row['id'], 'x')===false) {
			$row['id'] = vtws_getId($meta->getEntityId(), $row['id']);
		}

		if (isset($row[$recordString])) {
			$row['id'] = vtws_getId($meta->getEntityId(), $row[$recordString]);
			unset($row[$recordString]);
		}

		if (!isset($row['id'])) {
			if (!empty($row[$meta->getObectIndexColumn()])) {
				$row['id'] = vtws_getId($meta->getEntityId(), $row[$meta->getObectIndexColumn()]);
			}
		} elseif (isset($row[$meta->getObectIndexColumn()]) && strcmp($meta->getObectIndexColumn(), 'id')!==0) {
			unset($row[$meta->getObectIndexColumn()]);
		}

		foreach ($row as $field => $value) {
			$row[$field] = html_entity_decode($value, ENT_QUOTES, $default_charset);
		}
		return $row;
	}

	public static function sanitizeReferences($row, $meta, $uuid = false) {
		global $adb,$log;
		$references = $meta->getReferenceFieldDetails();
		$mname = strtolower($meta->getEntityName());
		foreach ($references as $field => $typeList) {
			if ($mname == 'emails' && $field=='parent_id' && isset($row['parent_id']) && strpos($row['parent_id'], '@')) {
				$refs = explode('|', trim($row['parent_id'], '|'));
				array_walk(
					$refs,
					function (&$val, $idx) {
						list($val, $fieldId) = explode('@', $val);
					}
				);
				$row['parent_id'] = $refs;
			}
			if (isset($row[$field])) {
				$setref = array();
				foreach ((array) $row[$field] as $refval) {
					if (strlen($refval)==40) {
						$refval = CRMEntity::getCRMIDfromUUID($refval);
					}
					$found = false;
					foreach ($typeList as $entity) {
						$webserviceObject = VtigerWebserviceObject::fromName($adb, $entity);
						$handlerPath = $webserviceObject->getHandlerPath();
						$handlerClass = $webserviceObject->getHandlerClass();
						require_once $handlerPath;
						$handler = new $handlerClass($webserviceObject, $meta->getUser(), $adb, $log);
						$entityMeta = $handler->getMeta();
						if ($entityMeta->exists($refval)) {
							if ($uuid && $webserviceObject->getEntityName()!='Users' && $webserviceObject->getEntityName()!='Currency') {
								$setref[] = $webserviceObject->getUUID($refval);
							} else {
								$setref[] = vtws_getId($webserviceObject->getEntityId(), $refval);
							}
							$found = true;
							break;
						}
					}
					if ($found !== true) {
						//This is needed as for query operation of the related record is deleted.
						$setref[] = null;
					}
				}
				$row[$field] = implode('|', $setref);
			} elseif (isset($row[$field]) && $row[$field]==0) {
				//0 is the default for most of the reference fields, so handle the case and return null instead as its the
				//only valid value, which is not a reference Id.
				$row[$field] = null;
			}
		}
		return $row;
	}

	public static function transformReferenceToUUID($row, $meta) {
		return DataTransform::sanitizeReferences($row, $meta, true);
	}

	public static function sanitizeOwnerFields($row, $meta, $t = null) {
		global $adb;
		$ownerFields = $meta->getOwnerFields();
		foreach ($ownerFields as $field) {
			if (isset($row[$field]) && $row[$field]!=null) {
				$ownerType = vtws_getOwnerType($row[$field]);
				$webserviceObject = VtigerWebserviceObject::fromName($adb, $ownerType);
				$row[$field] = vtws_getId($webserviceObject->getEntityId(), $row[$field]);
			}
		}
		return $row;
	}

	public static function sanitizeDateFieldsForDB($row, $meta) {
		global $current_user;
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			if ($fieldObj->getFieldDataType()=='date' && !empty($row[$fieldName])) {
				$dateFieldObj = new DateTimeField($row[$fieldName]);
				$row[$fieldName] = $dateFieldObj->getDBInsertDateValue($current_user);
			}
			if ($fieldObj->getFieldDataType()=='datetime' && !empty($row[$fieldName])) {
				$dateFieldObj = new DateTimeField($row[$fieldName]);
				$row[$fieldName] = substr($dateFieldObj->getDBInsertDateTimeValue(), 0, 16);
			}
		}
		return $row;
	}

	public static function sanitizeDateFieldsForInsert($row, $meta) {
		global $current_user;
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			if ($fieldObj->getFieldDataType()=='date' && !empty($row[$fieldName])) {
				$dateFieldObj = new DateTimeField($row[$fieldName]);
				$row[$fieldName] = $dateFieldObj->getDisplayDate($current_user);
			}
			if ($fieldObj->getFieldDataType()=='datetime' && !empty($row[$fieldName])) {
				$dateFieldObj = new DateTimeField($row[$fieldName]);
				$row[$fieldName] = substr($dateFieldObj->getDisplayDateTimeValue(), 0, 16);
			}
		}
		return $row;
	}

	public static function sanitizeCurrencyFieldsForInsert($row, $meta) {
		global $current_user;
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			if (($fieldObj->getFieldDataType()=='currency' || $fieldObj->getFieldDataType()=='double') && !empty($row[$fieldName])) {
				$uitype = $fieldObj->getUIType();
				$cryFields = new CurrencyField($row[$fieldName]);
				$cryFields->initialize($current_user);
				$cryFields->setNumberofDecimals($cryFields::$maxNumberOfDecimals);
				if ($uitype == '71') {
					$row[$fieldName] = $cryFields->getDisplayValue($current_user, false, true);
				} elseif ($uitype == '72' || $uitype == '7' || $uitype == '9') {
					$row[$fieldName] = $cryFields->getDisplayValue($current_user, true, true);
				}
			}
		}
		return $row;
	}

	public static function sanitizeCurrencyFieldsForDisplay($row, $meta) {
		global $current_user;
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			if (($fieldObj->getFieldDataType()=='currency' || $fieldObj->getFieldDataType()=='double') && !empty($row[$fieldName])) {
				$uitype = $fieldObj->getUIType();
				$cryFields = new CurrencyField($row[$fieldName]);
				$cryFields->initialize($current_user);
				if ($uitype == '71') {
					$row[$fieldName] = $cryFields->getDisplayValue($current_user, false, true);
				} elseif ($uitype == '72' || $uitype == '7' || $uitype == '9') {
					$row[$fieldName] = $cryFields->getDisplayValue($current_user, true, true);
				}
			}
		}
		return $row;
	}

	public static function sanitizeCurrencyFieldsForDB($row, $meta) {
		global $current_user;
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			if (($fieldObj->getFieldDataType()=='currency' || $fieldObj->getFieldDataType()=='double') && !empty($row[$fieldName])) {
				$cryFields = new CurrencyField($row[$fieldName]);
				$cryFields->initialize($current_user);
				$cryFields->setNumberofDecimals($cryFields::$maxNumberOfDecimals);
				$row[$fieldName] = $cryFields->getDBInsertedValue($current_user, true);
			}
		}
		return $row;
	}

	public static function sanitizeReferencesForDB($row, $meta) {
		$references = $meta->getReferenceFieldDetails();
		$mname = strtolower($meta->getEntityName());
		foreach ($references as $field => $typeList) {
			if ($mname == 'emails' && $field=='parent_id' && isset($row['parent_id']) && strpos($row['parent_id'], '|')) {
				$row['parent_id'] = explode('|', trim($row['parent_id'], '|'));
			}
			if (!empty($row[$field])) {
				$setref = array();
				foreach ((array) $row[$field] as $refval) {
					list($wsid, $crmid) = explode('x', $refval);
					$setref[] = $crmid;
				}
				$row[$field] = implode('|', $setref);
			}
			if (isset($row[$field]) && $row[$field]=='') {
				$row[$field] = null;
			}
		}
		return $row;
	}

	public static function sanitizeOwnerFieldsForDB($row, $meta) {
		$ownerFields = $meta->getOwnerFields();
		foreach ($ownerFields as $field) {
			if (!empty($row[$field])) {
				list($wsid, $row[$field]) = explode('x', $row[$field]);
			}
		}
		return $row;
	}

	public static function sanitizeTextFieldsForInsert($row, $meta) {
		global $default_charset;
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $fieldObj) {
			$ftype = $fieldObj->getFieldDataType();
			if (!empty($row[$fieldName]) && $ftype!='currency' && $ftype!='date') {
				$row[$fieldName] = html_entity_decode($row[$fieldName], ENT_QUOTES, $default_charset);
			}
		}
		return $row;
	}
}
?>
