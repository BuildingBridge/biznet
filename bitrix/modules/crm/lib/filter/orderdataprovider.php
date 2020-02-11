<?php
namespace Bitrix\Crm\Filter;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Counter\EntityCounterType;

Loc::loadMessages(__FILE__);

class OrderDataProvider extends EntityDataProvider
{
	/** @var InvoiceSettings|null */
	protected $settings = null;

	function __construct(OrderSettings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Get Settings
	 * @return InvoiceSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Get specified entity field caption.
	 * @param string $fieldID Field ID.
	 * @return string
	 */
	protected function getFieldName($fieldID)
	{
		$name = Loc::getMessage("CRM_ORDER_FILTER_{$fieldID}");
		if($name === null)
		{
//			$name = \CCrmInvoice::GetFieldCaption($fieldID);
		}

		return $name;
	}

	/**
	 * Prepare field list.
	 * @return Field[]
	 */
	public function prepareFields()
	{
		$result =  array(
			'ID' => $this->createField('ID'),
			'ACCOUNT_NUMBER' => $this->createField('ACCOUNT_NUMBER'),
			'ORDER_TOPIC' => $this->createField('ORDER_TOPIC'),
			'PRICE' => $this->createField('PRICE', array('type' => 'number', 'default' => true)),
			'DATE_INSERT' => $this->createField('DATE_INSERT', array('type' => 'date', 'default' => true)),
			'DATE_UPDATE' => $this->createField('DATE_UPDATE', array('type' => 'date')),
			'DEDUCTED' => $this->createField('DEDUCTED', array('type' => 'checkbox')),
			'PAYED' => $this->createField('PAYED', array('type' => 'checkbox')),
			'CANCELED' => $this->createField('CANCELED', array('type' => 'checkbox')),
			'USER' => $this->createField('USER',	array('default' => true)),
			'CREATED_BY' => $this->createField(
				'CREATED_BY',
				array('type' => 'custom_entity', 'partial' => true)
			),
			'RESPONSIBLE_ID' => $this->createField(
				'RESPONSIBLE_ID',
				array('type' => 'dest_selector', 'default' => true, 'partial' => true)
			),
			'STATUS_ID' => $this->createField(
				'STATUS_ID',
				array('default' => true, 'type' => 'list', 'partial' => true)
			),
			'PERSON_TYPE_ID' => $this->createField(
				'PERSON_TYPE_ID',
				array('type' => 'list', 'partial' => true)
			),
			'CURRENCY' => $this->createField(
				'CURRENCY',
				array('type' => 'list', 'partial' => true)
			),
			'CONTACT_ID' => $this->createField(
				'CONTACT_ID',
				array('type' => 'dest_selector', 'partial' => true)
			),
			'COMPANY_ID' => $this->createField(
				'COMPANY_ID',
				array('type' => 'dest_selector', 'partial' => true)
			),
			'ACTIVITY_COUNTER' => $this->createField(
				'ACTIVITY_COUNTER',
				array('type' => 'list', 'default' => true, 'partial' => true)
			),
			'SOURCE_ID' => $this->createField(
				'SOURCE_ID',
				array('type' => 'list', 'default' => true, 'partial' => true)
			)
		);

		return $result;
	}

	/**
	 * Prepare complete field data for specified field.
	 * @param string $fieldID Field ID.
	 * @return array|null
	 * @throws Main\NotSupportedException
	 */
	public function prepareFieldData($fieldID)
	{
		if ($fieldID === 'RESPONSIBLE_ID')
		{
			return array(
				'params' => array(
					'context' => 'CRM_ORDER_FILTER_RESPONSIBLE_ID',
					'multiple' => 'Y',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
					'isNumeric' => 'Y',
					'prefix' => 'U',
				)
			);
		}
		elseif ($fieldID === 'CREATED_BY')
		{
			return array(
				'selector' => array(
					'TYPE' => 'client',
					'DATA' => array('ID' => 'created_by', 'FIELD_ID' => 'CREATED_BY')
				)
			);
		}
		else if ($fieldID === 'STATUS_ID')
		{
			return array(
				'params' => array('multiple' => 'Y'),
				'items' =>  \Bitrix\Crm\Order\OrderStatus::getListInCrmFormat()
			);
		}
		else if ($fieldID === 'CURRENCY')
		{
			return array(
				'params' => array('multiple' => 'N'),
				'items' =>  \CCrmCurrencyHelper::PrepareListItems()
			);
		}
		else if ($fieldID === 'PERSON_TYPE_ID')
		{
			return array(
				'params' => array('multiple' => 'N'),
				'items' =>  \Bitrix\Crm\Order\PersonType::load(SITE_ID)
			);
		}
		elseif($fieldID === 'CONTACT_ID')
		{
			return array(
				'alias' => 'ASSOCIATED_CONTACT_ID',
				'params' => array(
					'apiVersion' => 3,
					'context' => 'CRM_ORDER_FILTER_CONTACT_ID',
					'contextCode' => 'CRM',
					'useClientDatabase' => 'N',
					'enableAll' => 'N',
					'enableDepartments' => 'N',
					'enableUsers' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
					'enableCrm' => 'Y',
					'enableCrmContacts' => 'Y',
					'convertJson' => 'Y'
				)
			);
		}
		elseif($fieldID === 'COMPANY_ID')
		{
			return array(
				'params' => array(
					'apiVersion' => 3,
					'context' => 'CRM_ORDER_FILTER_COMPANY_ID',
					'contextCode' => 'CRM',
					'useClientDatabase' => 'N',
					'enableAll' => 'N',
					'enableDepartments' => 'N',
					'enableUsers' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
					'enableCrm' => 'Y',
					'enableCrmCompanies' => 'Y',
					'convertJson' => 'Y'
				)
			);
		}
		elseif($fieldID === 'ACTIVITY_COUNTER')
		{
			return EntityCounterType::getListFilterInfo(
				array('params' => array('multiple' => 'Y')),
				array('ENTITY_TYPE_ID' => \CCrmOwnerType::Order)
			);
		}
		elseif($fieldID === 'SOURCE_ID')
		{
			return array(
				'params' => array('multiple' => 'Y'),
				'items' => $this->getSources()
			);
		}

		return null;
	}

	/**
	 * Get landings for filter
	 * @return array
	 */
	private function getSources()
	{
		$result = [];
		if (Main\Loader::includeModule('landing') && Main\Loader::includeModule('sale'))
		{
			$tradingPlatforms = [];
			$platformData = \Bitrix\Landing\Site::getList([
				'filter' => ['=TYPE' => 'STORE'],
				'select' => ['ID', 'TITLE']
			]);

			while ($landing = $platformData->fetch())
			{
				$code = \Bitrix\Sale\TradingPlatform\Landing\Landing::getCodeBySiteId($landing['ID']);
				$tradingPlatforms[$code] = $landing['TITLE'];
			}

			if (!empty($tradingPlatforms))
			{
				$platformsData = \Bitrix\Sale\TradingPlatformTable::getList([
					'select' => ['CODE', 'ID'],
					'filter' => ['=CLASS' => "\\".\Bitrix\Sale\TradingPlatform\Landing\Landing::class]
				]);

				while ($platform = $platformsData->fetch())
				{
					$code = $platform['CODE'];
					if (isset($tradingPlatforms[$code]))
					{
						$result[$platform['ID']] = $tradingPlatforms[$code];
					}
				}
			}
		}

		return $result;
	}
}