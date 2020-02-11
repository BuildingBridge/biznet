<?php
namespace Bitrix\Intranet\Component\UserProfile;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Form
{
	protected $userId = 0;
	protected $userFieldEntityId = 'USER';
	protected $userFields = null;
	protected $userFieldInfos = null;
	protected $userFieldDispatcher = null;


	function __construct($userId = 0)
	{
		$this->userId = $userId;
		$this->userFieldDispatcher = Main\UserField\Dispatcher::instance();
	}

	public function getFieldInfo($user, $availableFields = array())
	{
		global $USER;

		$isAdminRights = (
			Loader::includeModule("bitrix24") && \CBitrix24::IsPortalAdmin($USER->GetID())
			|| $USER->IsAdmin()
		)
			? true : false;

		$isExtranetUser = empty($user["UF_DEPARTMENT"]) ? true : false;

		$departmentList = array();
		if (Loader::includeModule("iblock"))
		{
			$departments = \CIBlockSection::GetTreeList(array(
				"IBLOCK_ID"=>intval(\COption::GetOptionInt('intranet', 'iblock_structure', false)),
			));
			while($department = $departments->Fetch())
			{
				$departmentList[] = array(
					'NAME' => /*str_repeat(" . ", $department["DEPTH_LEVEL"]).*/$department["NAME"],
					'VALUE' => $department["ID"]
				);
			}
		}

		$timeZoneItems = array();
		if(\CTimeZone::Enabled())
		{
			$timeZoneList = \CTimeZone::GetZones();
			foreach ($timeZoneList as $value => $name)
			{
				$timeZoneItems[] = array(
					"NAME" => $name,
					"VALUE" => $value
				);
			}
		}

		$personalCountryItems = array(
			array(
				"NAME" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_EMPTY"),
				"VALUE" => ""
			)
		);
		$countryList = GetCountryArray();
		foreach ($countryList["reference_id"] as $key => $id)
		{
			$personalCountryItems[] = array(
				"NAME" => $countryList["reference"][$key],
				"VALUE" => $id
			);
		}

		$personalBirthdayFormat = "j F Y";
		if ($user["PERSONAL_GENDER"] === "F")
		{
			if (\Bitrix\Main\Config\Option::get("intranet", "show_year_for_female", "N") === "N")
			{
				$personalBirthdayFormat = "j F";
			}
		}

		$fields = array(
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_NAME"),
				"name" => "NAME",
				"type" => "text",
				"editable" => true,
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_LAST_NAME"),
				"name" => "LAST_NAME",
				"type" => "text",
				"editable" => true,
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_SECOND_NAME"),
				"name" => "SECOND_NAME",
				"type" => "text",
				"editable" => true
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_EMAIL"),
				"name" => "EMAIL",
				"type" => "link",
				"data" => array(
					"link_template" => "mailto:#LINK#"
				),
				"editable" => true
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_WORK_POSITION"),
				"name" => "WORK_POSITION",
				"type" => "text",
				"editable" => true
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_BIRTHDAY"),
				"name" => "PERSONAL_BIRTHDAY",
				"type" => "datetime",
				"editable" => true,
				"data" =>  array(
					"enableTime" => false,
					"dateViewFormat" => $personalBirthdayFormat
				)
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_GENDER"),
				"name" => "PERSONAL_GENDER",
				"type" => "list",
				'data' => array(
					'items'=> array(
						array("NAME" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_EMPTY"), "VALUE" => ""),
						array('NAME' => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_GENDER_MALE"), 'VALUE' => "M"),
						array('NAME' => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_GENDER_FEMALE"), 'VALUE' => "F"),
					),
					"class" => "ui-ctl-w50"
				),
				"editable" => true
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_WWW"),
				"name" => "PERSONAL_WWW",
				"type" => "link",
				"data" => array(
					"target" => "_blank"
				),
				"editable" => true
			),

			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_MOBILE"),
				"name" => "PERSONAL_MOBILE",
				"type" => "text",
				"editable" => true
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_WORK_PHONE"),
				"name" => "WORK_PHONE",
				"type" => "text",
				"editable" => true
			),
			/*
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_UF_PHONE_INNER"),
				"name" => "UF_PHONE_INNER",
				"type" => "text",
				"editable" => true
			),
			*/
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_CITY"),
				"name" => "PERSONAL_CITY",
				"type" => "text",
				"editable" => true
			),
			/*
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_UF_SKYPE"),
				"name" => "UF_SKYPE",
				"type" => "link",
				"data" => array(
					"link_template" => "callto:#LINK#"
				),
				"editable" => true
			),
			*/
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_TIME_ZONE"),
				"name" => "TIME_ZONE",
				"type" => "timezone",
				'data' => array(
					'auto_timezone_items'=>  array(
						array('NAME' => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE_DEF"), 'VALUE' => ""),
						array('NAME' => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE_YES"), 'VALUE' => "Y"),
						array('NAME' => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_AUTO_TIME_ZONE_NO"), 'VALUE' => "N"),
					),
					'timezone_items'=> $timeZoneItems
				),
				"visibilityPolicy" => "edit",
				"editable" => true
			),
		);

		if (!$isExtranetUser)
		{
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_UF_DEPARTMENT"),
				"name" => "UF_DEPARTMENT",
				"type" => "multilist",
				'data' => array(
					'items'=> $departmentList,
					'class' => "ui-ctl-lg"
				),
				"editable" => $isAdminRights ? true : false
			);
		}

		if (!ModuleManager::isModuleInstalled("bitrix24"))
		{
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_LOGIN"),
				"name" => "LOGIN",
				"type" => "text",
				"editable" => true,
				"visibilityPolicy" => "edit",
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_COUNTRY"),
				"name" => "PERSONAL_COUNTRY",
				"type" => "list",
				"editable" => true,
				'data' => array(
					'items'=> $personalCountryItems,
					"class" => "ui-ctl-w50"
				),
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_FAX"),
				"name" => "PERSONAL_FAX",
				"type" => "text",
				"editable" => true
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_MAILBOX"),
				"name" => "PERSONAL_MAILBOX",
				"type" => "text",
				"editable" => true
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_PHONE"),
				"name" => "PERSONAL_PHONE",
				"type" => "text",
				"editable" => true
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_STATE"),
				"name" => "PERSONAL_STATE",
				"type" => "text",
				"editable" => true
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_STREET"),
				"name" => "PERSONAL_STREET",
				"type" => "text",
				"editable" => true
			);
			$fields[] = array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_PERSONAL_ZIP"),
				"name" => "PERSONAL_ZIP",
				"type" => "text",
				"editable" => true
			);
		}

		$result = array_merge($fields, array_values($this->getUserFieldInfos()));

		if (
			!empty($availableFields)
			&& is_array($availableFields)
		)
		{
			foreach ($result as $key => $field)
			{
				if (
					isset($field['name'])
					&& !in_array($field['name'], [ 'TIME_ZONE', 'UF_DEPARTMENT' ])
					&& !in_array($field['name'], $availableFields)
				)
				{
					unset($result[$key]);
				}
			}
			$result = array_values($result);
		}

		return $result;
	}

	public function getFieldInfoForEmailUser()
	{
		$fields = array(
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_NAME"),
				"name" => "NAME",
				"type" => "text",
				"editable" => true,
				'visibilityPolicy' => 'edit',
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_LAST_NAME"),
				"name" => "LAST_NAME",
				"type" => "text",
				"editable" => true,
				'visibilityPolicy' => 'edit',
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_SECOND_NAME"),
				"name" => "SECOND_NAME",
				"type" => "text",
				"editable" => true
			),
			array(
				"title" => Loc::getMessage("INTRANET_USER_PROFILE_FIELD_EMAIL"),
				"name" => "EMAIL",
				"type" => "link",
				"data" => array(
					"link_template" => "mailto:#LINK#"
				),
				"editable" => false
			)
		);

		return $fields;
	}

	public function getUserFields()
	{
		if($this->userFields !== null)
		{
			return $this->userFields;
		}

		$userFields = $this->userFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields($this->userFieldEntityId, $this->userId, LANGUAGE_ID);

		if (is_array($userFields))
		{
			$userFields = array_filter($userFields, function($userField) {
				static $ufReserved = [
					'UF_DEPARTMENT',
					'UF_USER_CRM_ENTITY',
					'UF_PUBLIC',
					'UF_TIMEMAN',
					'UF_TM_REPORT_REQ',
					'UF_TM_FREE',
					'UF_REPORT_PERIOD',
					'UF_1C',
					'UF_TM_ALLOWED_DELTA',
					'UF_SETTING_DATE',
					'UF_LAST_REPORT_DATE',
					'UF_DELAY_TIME',
					'UF_TM_REPORT_DATE',
					'UF_TM_DAY',
					'UF_TM_TIME',
					'UF_TM_REPORT_TPL',
					'UF_TM_MIN_DURATION',
					'UF_TM_MIN_FINISH',
					'UF_TM_MAX_START',
					'UF_CONNECTOR_MD5',
					'UF_WORK_BINDING',
					'UF_IM_SEARCH',
					'UF_BXDAVEX_CALSYNC',
					'UF_BXDAVEX_MLSYNC',
					'UF_UNREAD_MAIL_COUNT',
					'UF_BXDAVEX_CNTSYNC',
					'UF_BXDAVEX_MAILBOX',
					'UF_VI_PASSWORD',
					'UF_VI_BACKPHONE',
					'UF_VI_PHONE',
					'UF_VI_PHONE_PASSWORD'
				];

				return (!in_array($userField['FIELD_NAME'], $ufReserved));
			});
		}

		return(
			$userFields
		);
	}
	public function getUserFieldInfos()
	{
		if($this->userFieldInfos !== null)
		{
			return $this->userFieldInfos;
		}

		$this->userFieldInfos = array();
		$userFields = $this->getUserFields();

		$enumerationFields = array();
		foreach($userFields as $userField)
		{
			$fieldName = $userField['FIELD_NAME'];
			$fieldInfo = array(
				'USER_TYPE_ID' => $userField['USER_TYPE_ID'],
				'ENTITY_ID' => $this->userFieldEntityId,
				'ENTITY_VALUE_ID' => $this->userId,
				'FIELD' => $fieldName,
				'MULTIPLE' => $userField['MULTIPLE'],
				'MANDATORY' => $userField['MANDATORY'],
				'SETTINGS' => isset($userField['SETTINGS']) ? $userField['SETTINGS'] : null
			);

			if($userField['USER_TYPE_ID'] === 'enumeration')
			{
				$enumerationFields[$fieldName] = $userField;
			}

			$this->userFieldInfos[$fieldName] = array(
				'name' => $fieldName,
				'title' => isset($userField['EDIT_FORM_LABEL']) ? $userField['EDIT_FORM_LABEL'] : $fieldName,
				'type' => 'userField',
				'data' => array('fieldInfo' => $fieldInfo),
				'editable' => $userField['EDIT_IN_LIST'] == "Y" ? true : false
			);

			if(isset($userField['MANDATORY']) && $userField['MANDATORY'] === 'Y')
			{
				$this->userFieldInfos[$fieldName]['required'] = true;
			}
		}

		if(!empty($enumerationFields))
		{
			$enumInfos = $this->prepareEnumerationInfos($enumerationFields);
			foreach($enumInfos as $fieldName => $enums)
			{
				if(isset($this->userFieldInfos[$fieldName])
					&& isset($this->userFieldInfos[$fieldName]['data'])
					&& isset($this->userFieldInfos[$fieldName]['data']['fieldInfo'])
				)
				{
					$this->userFieldInfos[$fieldName]['data']['fieldInfo']['ENUM'] = $enums;
				}
			}
		}

		return $this->userFieldInfos;
	}
	protected function prepareEnumerationInfos(array $userFields)
	{
		$results = array();
		$map = array();
		$callbacks = array();
		foreach($userFields as $userField)
		{
			if(!isset($userField['USER_TYPE']['CLASS_NAME']))
			{
				continue;
			}

			$className = $userField['USER_TYPE']['CLASS_NAME'];
			if(!isset($callbacks[$className]))
			{
				$callbacks[$className] = array();
			}

			$callbacks[$className][] = $userField;
			$map[$userField['ID']] = $userField['FIELD_NAME'];
		}

		foreach($callbacks as $className => $userFields)
		{
			$enumResult = call_user_func_array(
				array($className, 'GetListMultiple'),
				array($userFields)
			);
			while($enum = $enumResult->GetNext())
			{
				if(!isset($enum['USER_FIELD_ID']))
				{
					continue;
				}

				$fieldID = $enum['USER_FIELD_ID'];
				if(!isset($map[$fieldID]))
				{
					continue;
				}

				$fieldName = $map[$fieldID];
				if(!isset($results[$fieldName]))
				{
					$results[$fieldName] = array();
				}

				$results[$fieldName][] = array('ID' => $enum['~ID'], 'VALUE' => $enum['~VALUE']);
			}
		}
		return $results;
	}
	public function getConfig($editableFields = array())
	{
		$elements = array();

		if (empty($editableFields) || ModuleManager::isModuleInstalled("bitrix24"))
		{
			$elements = array(
				array('name' => 'NAME'),
				array('name' => 'LAST_NAME'),
				array('name' => 'EMAIL'),
				array('name' => 'WORK_POSITION'),
				array('name' => 'UF_DEPARTMENT'),
				array('name' => 'SECOND_NAME'),
				array('name' => 'PERSONAL_BIRTHDAY'),
				array('name' => 'PERSONAL_GENDER'),
				array('name' => 'PERSONAL_WWW'),
				array('name' => 'PERSONAL_MOBILE'),
				array('name' => 'WORK_PHONE'),
				array('name' => 'UF_PHONE_INNER'),
				array('name' => 'PERSONAL_WWW'),
				array('name' => 'PERSONAL_CITY'),
				array('name' => 'UF_EMPLOYMENT_DATE'),
				array('name' => 'UF_SKYPE'),
				array('name' => 'TIME_ZONE'),
			);
		}
		else
		{
			foreach ($editableFields as $key => $field)
			{
				$elements[] = array('name' => $field);
			}
		}

		$formConfig = array(
			array(
				'name' => 'contact',
				'title' => Loc::getMessage("INTRANET_USER_PROFILE_SECTION_CONTACT_TITLE"),
				'type' => 'section',
				'elements' => $elements,
				'data' => array('isChangeable' => true, 'isRemovable' => false)
			)
		);

		return $formConfig;
	}

	public function getData($result)
	{
		$param = array(
			"NAME" => $result["User"]["NAME"],
			"LAST_NAME" => $result["User"]["LAST_NAME"],
			"SECOND_NAME" => $result["User"]["SECOND_NAME"]
		);
		$fullName = \CUser::FormatName(\CSite::GetNameFormat(), $param);

		$data = [
			"NAME" => $result["User"]["NAME"],
			"LAST_NAME" => $result["User"]["LAST_NAME"],
			"SECOND_NAME" => $result["User"]["SECOND_NAME"],
			"FULL_NAME" => $fullName,
			"LOGIN" => $result["User"]["LOGIN"],
			"WORK_POSITION" => $result["User"]["WORK_POSITION"],
			"PERSONAL_BIRTHDAY" => $result["User"]["PERSONAL_BIRTHDAY"],
			"PERSONAL_GENDER" => $result["User"]["PERSONAL_GENDER"],
			"PERSONAL_WWW" => $result["User"]["PERSONAL_WWW"],
			"UF_DEPARTMENT" => $result["User"]["UF_DEPARTMENT"],
			"PERSONAL_MOBILE" => $result["User"]["PERSONAL_MOBILE"],
			"WORK_PHONE" => $result["User"]["WORK_PHONE"],
			"UF_PHONE_INNER" => $result["User"]["UF_PHONE_INNER"],
			"PERSONAL_CITY" => $result["User"]["PERSONAL_CITY"],
			"EMAIL" => $result["User"]["EMAIL"],
			"UF_SKYPE" => $result["User"]["UF_SKYPE"],
			"TIME_ZONE" => [
				"timeZone" => $result["User"]["TIME_ZONE"],
				"autoTimeZone" => $result["User"]["AUTO_TIME_ZONE"]
			],
			'PERSONAL_COUNTRY' => $result["User"]["PERSONAL_COUNTRY"],
			'PERSONAL_FAX' => $result["User"]["PERSONAL_FAX"],
			'PERSONAL_MAILBOX' => $result["User"]["PERSONAL_MAILBOX"],
			'PERSONAL_PHONE' => $result["User"]["PERSONAL_PHONE"],
			'PERSONAL_STATE' => $result["User"]["PERSONAL_STATE"],
			'PERSONAL_STREET' => $result["User"]["PERSONAL_STREET"],
			'PERSONAL_ZIP' => $result["User"]["PERSONAL_ZIP"]
		];

		$userFields = $this->getUserFields();
		$userFieldInfos = $this->getUserFieldInfos();

		foreach($userFields as $fieldName => $userField)
		{
			$fieldValue = isset($userField['VALUE']) ? $userField['VALUE'] : '';
			$fieldData = isset($userFieldInfos[$fieldName])
				? $userFieldInfos[$fieldName] : null;

			if(!is_array($fieldData))
			{
				continue;
			}

			$isEmptyField = true;
			$fieldParams = $fieldData['data']['fieldInfo'];
			if((is_string($fieldValue) && $fieldValue !== '')
				|| (is_array($fieldValue) && !empty($fieldValue))
			)
			{
				$fieldParams['VALUE'] = $fieldValue;
				$isEmptyField = false;
			}

			$fieldSignature = $this->userFieldDispatcher->getSignature($fieldParams);
			if($isEmptyField)
			{
				$data[$fieldName] = array(
					'SIGNATURE' => $fieldSignature,
					'IS_EMPTY' => true
				);
			}
			else
			{
				$data[$fieldName] = array(
					'VALUE' => $fieldValue,
					'SIGNATURE' => $fieldSignature,
					'IS_EMPTY' => false
				);
			}
		}

		return $data;
	}

}