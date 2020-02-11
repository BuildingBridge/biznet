<?php

namespace Bitrix\Crm\History;

use Bitrix\Crm\DealTable;
use Bitrix\Crm\History\Entity\DealStageFullHistoryTable;

class DealStageFullHistoryEntry
{
	public static function synchronize($dealId)
	{
		$i = $dealId;
	}


	public static function processCagegoryChange($dealId)
	{
		self::unregister($dealId);
		self::register($dealId);
	}

	public static function register($dealId)
	{
		$existHistory = self::getDealHistoryById($dealId);
		$dealFullHistory = self::prepereFullHistoryFromHistory($existHistory);
		if ($existFullHistory = self::getDealFullHistoryById($dealId))
		{
			DealStageFullHistoryTable::update($existFullHistory['OWNER_ID'], $dealFullHistory);
		}
		else
		{
			DealStageFullHistoryTable::add($dealFullHistory);
		}

	}

	public static function unregister($dealId)
	{
		DealStageFullHistoryTable::deleteByDealId($dealId);
	}

	/**
	 * @param $dealId
	 *
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getDealFullHistoryById($dealId)
	{
		$history = DealStageFullHistoryTable::getHistoryByDealId($dealId);
		return $history;
	}

	/**
	 * @param $dealId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getDealHistoryById($dealId)
	{
		$dealHistoryQuery = DealTable::query();
		$dealHistoryQuery->addSelect('HISTORY.OWNER_ID', 'DEAL_ID');
		$dealHistoryQuery->addSelect('HISTORY.STAGE_ID', 'STAGE_ID_FROM_HISTORY');
		$dealHistoryQuery->addSelect('HISTORY.CREATED_DATE', 'HISTORY_CREATED_DATE');
		$dealHistoryQuery->addSelect('HISTORY.CATEGORY_ID', 'HISTORY_CATEGORY_ID');
		$dealHistoryQuery->addSelect('STAGE_SEMANTIC_ID', 'DEAL_STAGE_SEMANTIC_ID');
		$dealHistoryQuery->where('ID', $dealId);

		$dealHistory = $dealHistoryQuery->exec()->fetchAll();
		return $dealHistory;
	}


	private static function prepereFullHistoryFromHistory($existHistory)
	{
		$dealFullHistory = [];
		foreach ($existHistory as $historyChangedEntity)
		{
			if (!isset($dealFullHistory['HISTORY']))
			{
				$history = [
					$historyChangedEntity['STAGE_ID_FROM_HISTORY'] => $historyChangedEntity['HISTORY_CREATED_DATE']
				];

				$dealFullHistory = [
					'OWNER_ID' => $historyChangedEntity['DEAL_ID'],
					'CREATED_DATE' => $historyChangedEntity['HISTORY_CREATED_DATE'],
					'LAST_UPDATE_DATE' => $historyChangedEntity['HISTORY_CREATED_DATE'],
					'HISTORY' => $history,
					'CATEGORY_ID' => $historyChangedEntity['HISTORY_CATEGORY_ID'],
					'COMPLETED' => in_array($historyChangedEntity['DEAL_STAGE_SEMANTIC_ID'], ['S', 'F']) ? 'Y' : 'N'
				];
			}
			else
			{
				$dealFullHistory['LAST_UPDATE_DATE'] = $historyChangedEntity['HISTORY_CREATED_DATE'];
				$dealFullHistory['HISTORY'][$historyChangedEntity['STAGE_ID_FROM_HISTORY']] = $historyChangedEntity['HISTORY_CREATED_DATE'];
			}
		}

		return $dealFullHistory;
	}

}