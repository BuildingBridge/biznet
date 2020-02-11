<?
namespace Bitrix\Crm\Integration\Socialnetwork\Livefeed;

use \Bitrix\Socialnetwork\Livefeed\Provider;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Socialnetwork\LogTable;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/crm/lib/integration/socialnetwork/livefeed/crmlead.php');

final class CrmDeal extends Provider
{
	const PROVIDER_ID = 'CRM_DEAL';
	const CONTENT_TYPE_ID = 'CRM_DEAL';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array(
			\CCrmLiveFeedEvent::DealPrefix.\CCrmLiveFeedEvent::Add,
			\CCrmLiveFeedEvent::DealPrefix.\CCrmLiveFeedEvent::Client,
			\CCrmLiveFeedEvent::DealPrefix.\CCrmLiveFeedEvent::Progress,
			\CCrmLiveFeedEvent::DealPrefix.\CCrmLiveFeedEvent::Responsible,
			\CCrmLiveFeedEvent::DealPrefix.\CCrmLiveFeedEvent::Progress,
			\CCrmLiveFeedEvent::DealPrefix.\CCrmLiveFeedEvent::Message
		);
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Crm\Integration\Socialnetwork\Livefeed\CrmEntityComment();
		return $provider;
	}

	public function initSourceFields()
	{
		$entityId = $this->getEntityId();
		$logId = $this->getLogId();

		$fields = $entity = $logEntry = array();

		if ($entityId > 0)
		{
			$fields = array(
				'ID' => $entityId
			);

			$res = \CCrmDeal::getListEx(
				array(),
				array(
					'ID' => $entityId,
					'CHECK_PERMISSIONS' => 'N'
				),
				false,
				array('nTopCount' => 1),
				array()
			);
			if ($currentEntity = $res->fetch())
			{
				$fields['CURRENT_ENTITY'] = $currentEntity;
			}
		}

		if ($logId > 0)
		{
			$res = LogTable::getList(array(
				'filter' => array(
					'=ID' => $logId
				)
			));
			$logEntry = $res->fetch();
			if (
				!empty($logEntry['PARAMS'])
				&& !empty($fields['CURRENT_ENTITY'])
			) // not-message
			{
				$logEntry['PARAMS'] = unserialize($logEntry['PARAMS']);
				if (is_array($logEntry['PARAMS']))
				{
					$this->setSourceTitle($fields['CURRENT_ENTITY']['TITLE']);
					$fields = array_merge($fields, $logEntry['PARAMS']);

					$sourceDescription = \Bitrix\Crm\Integration\Socialnetwork::buildAuxTaskDescription(
						$logEntry['PARAMS'],
						\Bitrix\Crm\Integration\Socialnetwork::DATA_ENTITY_TYPE_CRM_DEAL
					);

					if (!empty($sourceDescription))
					{
						$this->setSourceDescription(Loc::getMessage('CRMINTEGRATION_SONETLF_ENTITY_DESCRIPTION', array(
							'#LOGENTRY_TITLE#' => $logEntry['TITLE'],
							'#ENTITY_TITLE#' => $sourceDescription
						)));
					}
				}
			}
			elseif ($logEntry['EVENT_ID'] == 'crm_deal_message')
			{
				$this->setSourceDescription($logEntry['MESSAGE']);
				$this->setSourceTitle(truncateText(($logEntry['TITLE'] != '__EMPTY__' ? $logEntry['TITLE'] : $logEntry['MESSAGE']), 100));
			}
		}

		$this->setSourceFields($fields);
	}

	public function getLiveFeedUrl()
	{
		$result = '';
		$logId = $this->getLogId();

		if ($logId > 0)
		{
			$result = "/crm/stream/?log_id=".$logId;
		}

		return $result;
	}
}