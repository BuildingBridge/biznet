<?php

namespace Bitrix\Crm\History\Entity;


use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\History\Entity\DealStageHistoryTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\Date;

class DealStageFullHistoryTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_crm_deal_stage_full_history';
	}

	public static function getMap()
	{
		$map = [
			new IntegerField('OWNER_ID', ['primary' => true]),
			new IntegerField('CATEGORY_ID'),
			new TextField('HISTORY'),
			new DateField('CREATED_DATE'),
			new DateField('LAST_UPDATE_DATE'),
			new StringField('COMPLETED'),
		];


		return $map;
	}

	public static function update($primary, array $data)
	{
		if (!empty($data['HISTORY']))
		{
			if (is_string($data['HISTORY']))
			{
				$data['HISTORY'] = unserialize($data['HISTORY']);
			}
			$data = self::adjustData($data);
			$data['HISTORY'] = serialize($data['HISTORY']);
			unset($data['OWNER_ID']);
		}

		if (!isset($data['CATEGORY_ID']) && is_null($data['CATEGORY_ID']))
		{
			$data['CATEGORY_ID'] = 0;
		}

		return parent::update($primary, $data);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getExistStageIdList()
	{
		static $stageIdsByCategories = [];

		if (!empty($stageIds))
		{
			return $stageIdsByCategories;
		}
		$categoriesIds = DealCategory::getAllIDs();

		foreach ($categoriesIds as $categoryId)
		{
			$stageListByCategory = DealCategory::getStageList($categoryId);
			foreach ($stageListByCategory as $stageId => $name)
			{
				$stageIdsByCategories[$categoryId][$stageId] = $stageId;
			}
		}


		return $stageIdsByCategories;
	}

	public static function add(array $data)
	{
		if (!empty($data['HISTORY']))
		{
			if (is_string($data['HISTORY']))
			{
				$data['HISTORY'] = unserialize($data['HISTORY']);
			}



			$data = self::adjustData($data);
			$data['HISTORY'] = serialize($data['HISTORY']);
		}

		if (!isset($data['CATEGORY_ID']) && is_null($data['CATEGORY_ID']))
		{
			$data['CATEGORY_ID'] = 0;
		}

		return parent::add($data);
	}


	private static function getExistStageIdListByCategoryId($id)
	{
		static $allStagesByCategoryId = null;
		if (!$allStagesByCategoryId)
		{
			$allStagesByCategoryId = self::getExistStageIdList();
		}

		if (!empty($allStagesByCategoryId[$id]))
		{
			return $allStagesByCategoryId[$id];
		}
		else
		{
			return [];
		}
	}

	private static function getDealUnSuccessStageListByCategoryId($id)
	{
		$namespace = DealCategory::prepareStageNamespaceID($id);
		$stageSemanticInfo = \CCrmStatus::GetDealStageSemanticInfo($namespace);

		$stageIds = self::getExistStageIdListByCategoryId($id);

		$unSuccessStageList = [];
		$firstUnSuccessStageId = $stageSemanticInfo['FINAL_UNSUCCESS_FIELD'];

		$firstUnSuccessStage = false;
		foreach ($stageIds as $stageId)
		{
			if ($stageId === $firstUnSuccessStageId)
			{
				$unSuccessStageList[] = $stageId;
				$firstUnSuccessStage = true;
			}

			if ($firstUnSuccessStage)
			{
				$unSuccessStageList[] = $stageId;
			}
		}

		return $unSuccessStageList;
	}
	/**
	 * @param array $data
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	protected static function adjustData(array $data)
	{
		if (!isset($data['CATEGORY_ID']))
		{
			throw new ArgumentException('CATEGORY_ID not exist in data');
		}

		if (!isset($data['HISTORY']))
		{
			throw new ArgumentException('HISTORY not exist in data');
		}
		$categoryId = $data['CATEGORY_ID'];
		$history = $data['HISTORY'];
		$sortedAllStages = self::getExistStageIdListByCategoryId($categoryId);
		$unSuccessDealStages = self::getDealUnSuccessStageListByCategoryId($categoryId);
		$sortedAllStagesDESC = array_reverse($sortedAllStages);

		$stageIdsFromHistory = array_keys($history);

		$lastStageFromHistory = end($stageIdsFromHistory);

		$lastDateInHistory = null;
		if (in_array($lastStageFromHistory, $unSuccessDealStages))
		{
			$previousOfLastStageFromHistory = $stageIdsFromHistory[count($stageIdsFromHistory) - 2];
			$isStartFillHistory = false;
			foreach ($sortedAllStagesDESC as $stageId)
			{
				if (!$isStartFillHistory && $stageId !== $previousOfLastStageFromHistory)
				{
					if ($stageId !== $lastStageFromHistory)
					{
						$history[$stageId] = null;
					}

					continue;
				}
				$isStartFillHistory = true;
				if (isset($history[$stageId]))
				{
					$lastDateInHistory = $history[$stageId];
				}
				else
				{
					$history[$stageId] = $lastDateInHistory;
				}

			}
		}
		else
		{
			foreach ($sortedAllStagesDESC as $stageId)
			{
				if (isset($history[$stageId]))
				{
					$lastDateInHistory = $history[$stageId];
				}
				else
				{
					$history[$stageId] = $lastDateInHistory;
				}
			}
		}





		$data['HISTORY'] = [];
		foreach ($sortedAllStages as $stageId)
		{
			/** @var Date|null $changeToStageDate */
			$changeToStageDate = $history[$stageId];
			$data['HISTORY'][$stageId] = $changeToStageDate !== null ? $changeToStageDate->format('Y-m-d') : null;
		}

		return $data;

	}

	public static function clean()
	{
		$tableName = self::getTableName();
		global $DB;
		$DB->Query('TRUNCATE TABLE ' . $tableName . ';');
	}

	/**
	 * @param $dealId
	 *
	 * @throws \Exception
	 */
	public static function deleteByDealId($dealId)
	{
		self::delete($dealId);
	}

	/**
	 * @param $dealId
	 *
	 * @return array|false
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getHistoryByDealId($dealId)
	{
		return self::getById($dealId)->fetchRaw();
	}
}