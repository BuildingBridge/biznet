<?php

namespace Bitrix\Crm\Agent\History;

use Bitrix\Crm\History\Entity\LeadStatusHistoryTable;
use Bitrix\Crm\History\LeadStatusHistoryWithSupposedEntry;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Update\Stepper;

/**
 * Class LeadStatusHistoryWithSupposed
 * @package Bitrix\Crm\Agent\History
 */
class LeadStatusHistoryWithSupposed extends Stepper
{
	const MAX_HISTORY_PER_ITERATION = 100;

	protected static $moduleId = 'crm';

	public function execute(array &$option)
	{

		/*hack because in table b_option field `name` has length 50 */
		if (static::class === LeadStatusHistoryWithSupposed::class || Option::get("crm", 'statisticPreCalculateLeadSupposedHistoryFinish', 'N') === 'Y')
		{
			return false;
		}
		$lastStoredHistoryId = Option::get('crm', 'analyticsLeadSupposedHistoryLastExecutedHistoryId', 0);
		$executedStepCount = Option::get("crm", 'analyticsLeadSupposedHistoryExecutedStepCount', 0);

		if ($executedStepCount == 0)
		{
			Option::set("crm", 'statisticPreCalculateLeadSupposedHistoryFinish', 'N');
			$lastStoredHistoryId = $this->getLastStoredLeadStatusHistoryId();
		}

		$histories = $this->getHistories($lastStoredHistoryId);

		if (empty($histories))
		{
			Option::delete("crm", ['name' => 'analyticsLeadSupposedHistoryLastExecutedHistoryId']);
			Option::delete("crm", ['name' => 'analyticsLeadSupposedHistoryExecutedStepCount']);
			Option::set("crm", 'statisticPreCalculateLeadSupposedHistoryFinish', 'Y');
			$this->dropLastStoredLeadIdTable();

			return false;
		}

		$executedLeadIds = [];
		foreach ($histories as $history)
		{
			$lastStoredHistoryId = $history['ID'];
			$executedLeadIds[] = $history['OWNER_ID'];
		}

		$leadIds = array_unique($executedLeadIds);
		foreach ($leadIds as $id)
		{
			LeadStatusHistoryWithSupposedEntry::copyHistoryFromExist($id);
		}

		$executedStepCount++;

		Option::set("crm", 'analyticsLeadSupposedHistoryLastExecutedHistoryId', $lastStoredHistoryId);
		Option::set("crm", 'analyticsLeadSupposedHistoryExecutedStepCount', $executedStepCount);

		$option["steps"] = $executedStepCount;
		$option["count"] = (int)(($this->getHistoryCount() / self::MAX_HISTORY_PER_ITERATION) + 1);

		return true;
	}

	private function getLastStoredLeadStatusHistoryId()
	{
		static $result = null;
		if (is_null($result))
		{
			global $DB;
			$result = $DB->Query('SELECT LEAD_STATUS_HISTORY_ID FROM b_crm_last_stored_lead_status_history_id;');
			$result = $result->fetch();
			if (!empty($result['LEAD_STATUS_HISTORY_ID']))
			{
				$result = (int)$result['LEAD_STATUS_HISTORY_ID'];
			}
			else
			{
				$result = 0;
			}
		}

		return $result;
	}

	private function getHistoryCount()
	{
		$result = LeadStatusHistoryTable::query()->addSelect(Query::expr()->count('ID'), 'CNT')->exec()->fetchRaw();

		return $result['CNT'];
	}

	private function getHistories($fromHistoryId = 0)
	{
		$query = LeadStatusHistoryTable::query();
		$query->addSelect('ID');
		$query->addSelect('OWNER_ID');
		$query->where('ID', '>', (int)$fromHistoryId);
		$query->setLimit(self::MAX_HISTORY_PER_ITERATION);

		return $query->exec()->fetchAll();
	}

	private function dropLastStoredLeadIdTable()
	{
		global $DB;
		$DB->Query('DROP TABLE b_crm_last_stored_lead_status_history_id;');
	}
}