<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/configs/perms/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?> <?$APPLICATION->IncludeComponent(
	"bitrix:crm.config.perms",
	"",
	Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/configs/perms/",
		"SEF_URL_TEMPLATES" => Array(
			"PATH_TO_ENTITY_LIST" => "",
			"PATH_TO_ROLE_EDIT" => "#role_id#/edit/"
		),
		"VARIABLE_ALIASES" => Array(
			"PATH_TO_ENTITY_LIST" => Array(),
			"PATH_TO_ROLE_EDIT" => Array(),
		)
	)
);?> <?


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>