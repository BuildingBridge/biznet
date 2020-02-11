<?php
namespace Bitrix\Crm\Integration\Sender;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Sender;
use Bitrix\Crm;

Loc::loadMessages(__FILE__);

class Segment extends Main\Engine\Controller
{
	/**
	 * Upload entity emails/phone_numbers to new or existed segment.
	 * Example:
	 * BX.ajax.runAction(
	 * 	"crm.integration.sender.segment.upload",
	 * 	{ data: { id: null, entityTypeName: "COMPANY", entities: [1,2,3] }}
	 * );
	 *
	 * @param int|null $segmentId Segment ID.
	 * @param string $entityTypeName Entity type name.
	 * @param array $entities Entities.
	 * @param string $gridId Grid ID.
	 * @return array
	 */
	public function uploadAction($segmentId = null, $entityTypeName, array $entities, $gridId = null)
	{
		if (!GridPanel::canCurrentUserModifySegments())
		{
			return ['errors' => ['Access denied.']];
		}

		$entityTypeId = \CCrmOwnerType::resolveID($entityTypeName);
		if (!$entityTypeId)
		{
			return ['errors' => ['Wrong entity type.']];
		}
		if (!in_array($entityTypeId, [\CCrmOwnerType::Contact, \CCrmOwnerType::Company, \CCrmOwnerType::Lead]))
		{
			return ['errors' => ['Entity type does not allowed.']];
		}

		$segment = new Sender\Entity\Segment($segmentId);
		if (!$segment->getId())
		{
			$segmentName = Loc::getMessage(
				'CRM_INTEGRATION_SENDER_SEGMENT_NAME_PATTERN_' . $entityTypeName,
				['%date%' => Sender\Internals\PrettyDate::formatDate()]
			);
			$segment
				->set('NAME', $segmentName)
				->set('HIDDEN', 'N')
				->appendContactSetConnector()
				->save();
			if ($segment->hasErrors())
			{
				return ['errors' => $segment->getErrorMessages()];
			}
		}

		if ($gridId)
		{
			$entitiesByGridFilter = self::getEntitiesByGridId($entityTypeId, $gridId);
			$entities = empty($entitiesByGridFilter) ? $entities : $entitiesByGridFilter;
		}

		$segment->upload(self::getAddresses($entityTypeName, $entities));
		if ($segment->hasErrors())
		{
			return ['errors' => $segment->getErrorMessages()];
		}

		$segmentId = $segment->getId();
		$segmentName = $segment->get('NAME');
		$textSuccess = Loc::getMessage(
			'CRM_INTEGRATION_SENDER_SEGMENT_UPLOAD_SUCCESS',
			[
				'%name%' => $segmentName,
				'%path%' => '/marketing/segment/'
			]
		);

		return [
			'id' => $segmentId,
			'name' => $segmentName,
			'textSuccess' => str_replace('%name%', $segmentName, $textSuccess),
		];
	}

	protected static function getAddresses($entityTypeName, array $entities)
	{
		switch ($entityTypeName)
		{
			case \CCrmOwnerType::CompanyName:
				$entityObject = new \CCrmCompany();
				break;
			case \CCrmOwnerType::ContactName:
				$entityObject = new \CCrmContact();
				break;
			case \CCrmOwnerType::LeadName:
				$entityObject = new \CCrmLead();
				break;

			default:
				return [];
		}

		$listDb = $entityObject->getListEx([], ['ID' => $entities], false, false, ['ID', 'NAME', 'TITLE', 'CONTACT_NAME']);
		$entities = [];
		while ($entity = $listDb->Fetch())
		{
			$entityName = isset($entity['TITLE']) ? $entity['TITLE'] : null;
			$entityName = isset($entity['NAME']) ? $entity['NAME'] : $entityName;
			$entityName = isset($entity['CONTACT_NAME']) ? $entity['CONTACT_NAME'] : $entityName;
			$entities[$entity['ID']] = $entityName;
		}

		$result = [];

		$typeMap = [
			\CCrmFieldMulti::PHONE => Sender\Recipient\Type::PHONE,
			\CCrmFieldMulti::EMAIL => Sender\Recipient\Type::EMAIL,
		];
		$list = Crm\FieldMultiTable::getList([
			'select' => ['TYPE_ID', 'VALUE', 'ELEMENT_ID'],
			'filter' => [
				'=ENTITY_ID' => $entityTypeName,
				'=ELEMENT_ID' => array_keys($entities),
				'=TYPE_ID' => array_keys($typeMap)
			],
		]);
		foreach ($list as $item)
		{
			$result[] = [
				'CODE' => $item['VALUE'],
				'NAME' => isset($entities[$item['ELEMENT_ID']])
					? $entities[$item['ELEMENT_ID']]
					: null
				,
			];
		}

		return $result;
	}

	private static function getEntitiesByGridId($entityTypeId, $gridId)
	{
		if (!$entityTypeId || !$gridId)
		{
			return [];
		}

		$filterOptions = new Main\UI\Filter\Options($gridId);
		$filterFields = $filterOptions->getFilter();
		$entityFilter = Crm\Filter\Factory::createEntityFilter(
			Crm\Filter\Factory::createEntitySettings($entityTypeId, $gridId)
		);

		$entityFilter->prepareListFilterParams($filterFields);
		Crm\Search\SearchEnvironment::convertEntityFilterValues($entityTypeId, $filterFields);
		\CCrmEntityHelper::PrepareMultiFieldFilter($filterFields, array(), '=%', false);

		$entity = Crm\Entity\EntityManager::resolveByTypeID($entityTypeId);
		if($entity)
		{
			$entity->prepareFilter($filterFields, []);
			return $entity->getTopIDs(['filter' => $filterFields]);
		}

		return [];
	}
}