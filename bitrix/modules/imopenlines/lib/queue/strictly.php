<?php
namespace Bitrix\ImOpenLines\Queue;

use \Bitrix\Main\Type\DateTime;

use \Bitrix\ImOpenLines;

/**
 * Class Strictly
 * @package Bitrix\ImOpenLines\Queue
 */
class Strictly extends Queue
{
	/**
	 * @param int $currentOperator
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getOperatorsQueue($currentOperator = 0)
	{
		$queueTime = ImOpenLines\Queue::UNDISTRIBUTED_QUEUE_TIME;
		$isOfflineOperator = false;

		$result = [
			'RESULT' => false,
			'OPERATOR_ID' => 0,
			'OPERATOR_LIST' => [],
			'DATE_QUEUE' => (new DateTime())->add($queueTime . ' SECONDS'),
			'QUEUE_HISTORY' => [],
		];

		$operators = [];
		$queueHistory = $this->session['QUEUE_HISTORY'];
		$operatorId = 0;

		$select = [
			'ID',
			'USER_ID'
		];
		if($this->config['CHECK_ONLINE'] == 'Y')
		{
			$select[] = 'IS_ONLINE_CUSTOM';
		}
		$filter = ['=CONFIG_ID' => $this->config['ID']];
		$order = ['ID' => 'asc'];

		$res = ImOpenLines\Queue::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order
		]);

		while($queueUser = $res->fetch())
		{
			if($this->isOperatorAvailable($queueUser['USER_ID'], $currentOperator))
			{
				if($this->config['CHECK_ONLINE'] != 'Y' ||
					$queueUser['IS_ONLINE_CUSTOM'] == 'Y')
				{
					$operators[$queueUser['USER_ID']] = $queueUser;
				}
				else
				{
					$isOfflineOperator = true;
				}
			}
		}

		if(!empty($operators))
		{
			foreach ($operators as $operator)
			{
				if(!empty($queueHistory[$operator['USER_ID']]))
				{
					continue;
				}

				$operatorId = $operator['USER_ID'];

				break;
			}

			if(empty($operatorId))
			{
				$operatorId = reset($operators)['USER_ID'];
				$queueHistory = [$operatorId => true];
			}
			else
			{
				$queueHistory[$operatorId] = true;
			}

			if($this->config['QUEUE_TIME'] > 0)
			{
				$queueTime = $this->config['QUEUE_TIME'];
			}

			$result = [
				'RESULT' => true,
				'OPERATOR_ID' => $operatorId,
				'OPERATOR_LIST' => [$operatorId],
				'DATE_QUEUE' => (new DateTime())->add($queueTime . ' SECONDS'),
				'QUEUE_HISTORY' => $queueHistory,
			];
		}
		elseif($isOfflineOperator == true)
		{
			if($this->config['QUEUE_TIME'] > 0)
			{
				$queueTime = $this->config['QUEUE_TIME'];
				$result['DATE_QUEUE'] = (new DateTime())->add($queueTime . ' SECONDS');
			}
		}

		return $result;
	}
}