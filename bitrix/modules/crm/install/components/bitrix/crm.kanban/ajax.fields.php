<?
/**
 * Full copy from crm.lead.list/filter.ajax.php
 * with some changes from crm.deal.list/filter.ajax.php
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

// init request section
$siteID = isset($_REQUEST['site']) ? $_REQUEST['site'] : '';
$entityType = isset($_REQUEST['entityType']) ? $_REQUEST['entityType'] : '';
$viewType = isset($_REQUEST['viewType']) ? $_REQUEST['viewType'] : '';
$filterId = isset($_REQUEST['filter_id']) ? $_REQUEST['filter_id'] : 'CRM_LEAD_LIST_V12';
$siteID = substr(preg_replace('/[^a-z0-9_]/i', '', $siteID), 0, 2);
$entityType = strtolower($entityType);
$viewType = strtolower($viewType);

if ($siteID !== '')
{
	define('SITE_ID', $siteID);
}
if (!in_array($entityType, ['lead', 'deal']))
{
	$entityType = 'lead';
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!Main\Loader::includeModule('crm'))
{
	$result = ['ERROR' => 'Module is not installed'];
}
elseif (!check_bitrix_sessid())
{
	$result = ['ERROR' => 'Access denied'];
}
elseif (
	($entityType == 'lead' && !(\CCrmLead::checkReadPermission())) ||
	($entityType == 'deal' && !(\CCrmDeal::checkReadPermission()))
)
{
	$result = ['ERROR' => 'Access denied'];
}
else
{
	// additional fields, which we want to add
	$generalFields = [
		'OBSERVER' => [
			'ID' => 'field_OBSERVER',
			'NAME' => 'OBSERVER',
			'LABEL' => Loc::getMessage('CRM_KANBAN_FIELD_OBSERVER')
		],
		'SOURCE_DESCRIPTION' => [
			'ID' => 'field_SOURCE_DESCRIPTION',
			'NAME' => 'SOURCE_DESCRIPTION',
			'LABEL' => Loc::getMessage('CRM_KANBAN_FIELD_SOURCE_DESCRIPTION')
		]
	];
	/*if ($viewType == 'edit' && $entityType == 'deal')
	{
		$generalFields['RECURRING'] = [
			'ID' => 'field_RECURRING',
			'NAME' => 'RECURRING',
			'LABEL' => Loc::getMessage('CRM_KANBAN_FIELD_RECURRING')
		];
	}*/

	// get filter's fields as etalon of fields array
	$filter = \Bitrix\Crm\Filter\Factory::createEntityFilter(
		($entityType == 'lead')
		? new \Bitrix\Crm\Filter\LeadSettings(
			['ID' => $filterId]
		)
		: new \Bitrix\Crm\Filter\DealSettings(
			['ID' => $filterId]
		)
	);

	// prepare fields
	$result = [];
	foreach ($filter->getFields() as $field)
	{
		if ($generalFields)
		{
			if (substr($field->getId(), 0, 3) == 'UF_')
			{
				$result = array_merge(
					$result,
					$generalFields
				);
				$generalFields = [];
			}
		}
		$result[$field->getId()] = Main\UI\Filter\FieldAdapter::adapt($field->toArray(
			['lightweight' => true]
		));
	}

	// fill result with user fields
	$userTypeManager = new \CCrmUserType(
		$USER_FIELD_MANAGER,
		'CRM_' . strtoupper($entityType)
	);
	$labelCodes = [
		'LIST_FILTER_LABEL', 'LIST_COLUMN_LABEL', 'EDIT_FORM_LABEL'
	];
	foreach ($userTypeManager->getFields() as $fieldName => $userField)
	{
		// if field does't exist, put it
		if (!isset($result[$fieldName]))
		{
			// detect field's label
			$fieldLabel = '';
			foreach ($labelCodes as $code)
			{
				if (isset($userField[$code]))
				{
					$fieldLabel = trim($userField[$code]);
					if ($fieldLabel)
					{
						break;
					}
				}
			}
			if (!$fieldLabel)
			{
				$fieldLabel = $fieldName;
			}
			// add to the result
			$result[$fieldName] =  [
				'ID' => 'field_' . $fieldName,
				'NAME' => $fieldName,
				'LABEL' => $fieldLabel
			];
		}
		// delete uf-fields by some types
		if (
			isset($userField['USER_TYPE_ID']) &&
			isset($result[$fieldName])
		)
		{
			if ($userField['USER_TYPE_ID'] == 'resourcebooking')
			{
				unset($result[$fieldName]);
				continue;
			}
			if (
				$viewType == 'edit' &&
				$userField['USER_TYPE_ID'] == 'money'
			)
			{
				unset($result[$fieldName]);
				continue;
			}
		}
	}

	// replace some fields
	if (isset($result['OPPORTUNITY']))
	{
		$result['OPPORTUNITY']['LABEL'] = Loc::getMessage('CRM_KANBAN_FIELD_OPPORTUNITY_WITH_CURRENCY');
	}

	// remove some common fields
	$unAcceptableFields = [
		'STAGE_ID', 'STATUS', 'STATUS_ID'
	];
	foreach ($unAcceptableFields as $code)
	{
		if (isset($result[$code]))
		{
			unset($result[$code]);
		}
	}
}

$response = new Main\HttpResponse(
	Main\Application::getInstance()->getContext()
);
$response->addHeader('Content-Type', 'application/json');
$response->flush(Main\Web\Json::encode(array_values($result)));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();