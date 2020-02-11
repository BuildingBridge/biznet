<?php

namespace Bitrix\Crm\Agent\History;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\History\Entity\DealStageFullHistoryTable;
use Bitrix\Crm\History\Entity\DealStageHistoryTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Update\Stepper;

/**
 * Class DealStageFullHistory
 * @package Bitrix\Crm\Agent\History
 */
class DealStageFullHistory extends Stepper
{
	protected static $moduleId = "crm";
	const MAX_DEAL_PER_ITERATION = 100;

	/**
	 * Executes some action, and if return value is false, agent will be deleted.
	 *
	 * @param array $option Array with main data to show if it is necessary like {steps : 35, count : 7}, where steps is an amount of iterations, count - current position.
	 *
	 * @return boolean
	 */
	public function execute(array &$option)
	{
		$lastExecutedDealId = Option::get("crm", 'analyticsDealHistoryLastExecutedDealId', 0);
		$executedStepCount = Option::get("crm", 'analyticsDealHistoryExecutedStepCount', 0);

		if ($executedStepCount == 0)
		{
			Option::set("crm", 'statisticPreCalculateFullHistoryFinish', false);
			$this->cleanFullHistoryTables();
		}


		$history = $this->getDealsHistory($lastExecutedDealId);
		if (empty($history))
		{
			Option::delete("crm", array('name' => 'analyticsDealHistoryLastExecutedDealId'));
			Option::delete("crm", array('name' => 'analyticsDealHistoryExecutedStepCount'));
			Option::set("crm", 'statisticPreCalculateFullHistoryFinish', true);
			return false;
		}

		$dealsFullHistory = [];
		foreach ($history as $historyChangedEntity)
		{
			if ($historyChangedEntity['DEAL_ID'] > $lastExecutedDealId)
			{
				$lastExecutedDealId = $historyChangedEntity['DEAL_ID'];
			}

			if (!isset($dealsFullHistory[$historyChangedEntity['DEAL_ID']]))
			{
				$history = [
					$historyChangedEntity['STAGE_ID_FROM_HISTORY'] => $historyChangedEntity['HISTORY_CREATED_DATE']
				];

				$dealsFullHistory[$historyChangedEntity['DEAL_ID']] = [
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
				$dealsFullHistory[$historyChangedEntity['DEAL_ID']]['LAST_UPDATE_DATE'] = $historyChangedEntity['HISTORY_CREATED_DATE'];
				$dealsFullHistory[$historyChangedEntity['DEAL_ID']]['HISTORY'][$historyChangedEntity['STAGE_ID_FROM_HISTORY']] = $historyChangedEntity['HISTORY_CREATED_DATE'];
			}
		}

		foreach ($dealsFullHistory as &$dealFullHistory)
		{
			$dealFullHistory['HISTORY'] = serialize($dealFullHistory['HISTORY']);

			if ($existFullHistory = self::getDealFullHistoryById($dealFullHistory['OWNER_ID']))
			{
				unset($dealFullHistory['OWNER_ID']);
				DealStageFullHistoryTable::update($existFullHistory['OWNER_ID'], $dealFullHistory);
			}
			else
			{
				DealStageFullHistoryTable::add($dealFullHistory);
			}
		}



		$executedStepCount++;

		Option::set("crm", 'analyticsDealHistoryLastExecutedDealId', $lastExecutedDealId);
		Option::set("crm", 'analyticsDealHistoryExecutedStepCount', $executedStepCount);

		$option["steps"] = $executedStepCount;
		$option["count"] = (int)(($this->getDealsCount() / self::MAX_DEAL_PER_ITERATION) + 1);

		return true;
	}

	/**
	 * @param int $fromDealId
	 *
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getDealsHistory($fromDealId = 0)
	{
		$fromDealId = (int)$fromDealId;
		$dealSubQuery = DealTable::query();
		$dealSubQuery->addSelect('ID');
		$dealSubQuery->addSelect('STAGE_SEMANTIC_ID');
		$dealSubQuery->setLimit(self::MAX_DEAL_PER_ITERATION);
		$dealSubQuery->setOrder('ID');
		$dealSubQuery->where('ID', '>', $fromDealId);


		$dealSubEntity = Entity::compileEntity('dealSubEntity', [
			new IntegerField('ID'),
			new StringField('STAGE_SEMANTIC_ID')
		], ['table_name' => '(' . $dealSubQuery->getQuery() . ')']);

		$runtimeField = new ReferenceField('DEALS', $dealSubEntity, Join::on('this.OWNER_ID', 'ref.ID'), [
			'join_type' => 'INNER'
		]);



		$dealHistoryQuery = DealStageHistoryTable::query();
		$dealHistoryQuery->registerRuntimeField('DEAL', $runtimeField);
		$dealHistoryQuery->addSelect('OWNER_ID', 'DEAL_ID');
		$dealHistoryQuery->addSelect('STAGE_ID', 'STAGE_ID_FROM_HISTORY');
		$dealHistoryQuery->addSelect('CREATED_DATE', 'HISTORY_CREATED_DATE');
		$dealHistoryQuery->addSelect('CATEGORY_ID', 'HISTORY_CATEGORY_ID');
		$dealHistoryQuery->addSelect('DEAL.STAGE_SEMANTIC_ID', 'DEAL_STAGE_SEMANTIC_ID');

		$dealsHistories = $dealHistoryQuery->exec()->fetchAll();
		return $dealsHistories;
	}


	private function getDealFullHistoryById($dealId)
	{
		$history = DealStageFullHistoryTable::getHistoryByDealId($dealId);
		return $history;
	}
	/**
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getDealsCount()
	{
		$result = DealTable::query()
							->addSelect(Query::expr()->count('ID'), 'CNT')
							->exec()->fetchRaw();

		return $result['CNT'];
	}

	private function cleanFullHistoryTables()
	{
		DealStageFullHistoryTable::clean();
	}

	private function getStageList($categoryId)
	{
		$stageList = [];
		$stageListByCategory = DealCategory::getStageList($categoryId);
		$entityId = $categoryId == 0 ? 'DEAL_STAGE' : 'DEAL_STAGE_' . $categoryId;
		foreach ($stageListByCategory as $stageId => $name)
		{
			$stageList[$stageId] = [
				'NAME' => $name,
				'STATUS_ID' => $stageId,
				'ENTITY_ID' => $entityId
			];
		}

		return $stageList;
	}
}