<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$APPLICATION->SetTitle(Loc::getMessage("CRM_SITE_MASTER_TITLE"));

// check permissions
/** @noinspection PhpVariableNamingConventionInspection */
global $USER;
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(Loc::getMessage("CRM_SITE_MASTER_ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$defaultStep = [
	"Bitrix\Wizard\Steps\WelcomeStep" => [
		"SORT" => 100
	],
	"Bitrix\Wizard\Steps\BackupStep" => [
		"SORT" => 200
	],
	"Bitrix\Wizard\Steps\SiteInstructionStep" => [
		"SORT" => 300
	],
	"Bitrix\Wizard\Steps\SiteStep" => [
		"SORT" => 400
	],
];

if (Bitrix\Main\ModuleManager::isModuleInstalled("intranet"))
{
	$defaultStep["Bitrix\Wizard\Steps\DataInstallStep"]["SORT"] = 500;
	$defaultStep["Bitrix\Wizard\Steps\CrmGroupStep"]["SORT"] = 600;
	$defaultStep["Bitrix\Wizard\Steps\FinishStep"]["SORT"] = 700;
}

$APPLICATION->IncludeComponent(
	'bitrix:sale.crm.site.master',
	'',
	array(
		"DEFAULT_STEPS" => $defaultStep,
		"REQUIRED_STEPS" => [
			"Bitrix\Wizard\Steps\B24ConnectorStep" => [
				"SORT" => 120
			],
			"Bitrix\Wizard\Steps\PersonTypeStep" => [
				"SORT" => 130
			],
			"Bitrix\Wizard\Steps\ActivationKeyStep" => [
				"SORT" => 140
			],
			"Bitrix\Wizard\Steps\UpdateSystemStep" => [
				"SORT" => 150
			],
			"Bitrix\Wizard\Steps\ModuleStep" => [
				"SORT" => 160
			],
			"Bitrix\Wizard\Steps\ModuleInstallStep" => [
				"SORT" => 170
			]
		],
		"MODULES_REQUIRED" => [
			"main" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_MAIN_NAME"),
				"version" => "19.0.400"
			],
			"pull" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_PULL_NAME"),
				"version" => "19.0.0"
			],
			"intranet" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_INTRANET_NAME"),
				"version" => "19.0.600"
			],
			"crm" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_CRM_NAME"),
				"version" => "19.0.300"
			],
			"tasks" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_TASKS_NAME"),
				"version" => ""
			],
			"disk" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_DISK_NAME"),
				"version" => ""
			],
			"calendar" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_CALENDAR_NAME"),
				"version" => ""
			],
			"im" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_IM_NAME"),
				"version" => "16.5.0"
			],
			"webdav" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_WEBDAV_NAME"),
				"version" => ""
			],
			"dav" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_DAV_NAME"),
				"version" => ""
			],
			"timeman" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_TIMEMAN_NAME"),
				"version" => ""
			],
			"meeting" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_MEETING_NAME"),
				"version" => ""
			],
			"replica" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_REPLICA_NAME"),
				"version" => ""
			],
			"imconnector" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_IMCONNECTOR_NAME"),
				"version" => ""
			],
			"mobile" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_MOBILE_NAME"),
				"version" => ""
			],
			"mobileapp" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_MOBILEAPP_NAME"),
				"version" => ""
			],
			"voximplant" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_VOXIMPLANT_NAME"),
				"version" => ""
			],
			"imopenlines" => [
				"name" => Loc::getMessage("CRM_SITE_MASTER_MODULE_IMOPENLINES_NAME"),
				"version" => ""
			],
		]
	)
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");