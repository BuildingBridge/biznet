<?
namespace Bitrix\Crm\Integration\Socialnetwork\Livefeed;

use \Bitrix\Socialnetwork\Livefeed\Provider;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

final class CrmLead extends Provider
{
	const PROVIDER_ID = 'CRM_LEAD';
	const CONTENT_TYPE_ID = 'CRM_LEAD';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array(
			\CCrmLiveFeedEvent::LeadPrefix.\CCrmLiveFeedEvent::Add,
			\CCrmLiveFeedEvent::LeadPrefix.\CCrmLiveFeedEvent::Progress,
			\CCrmLiveFeedEvent::LeadPrefix.\CCrmLiveFeedEvent::Denomination,
			\CCrmLiveFeedEvent::LeadPrefix.\CCrmLiveFeedEvent::Responsible,
			\CCrmLiveFeedEvent::LeadPrefix.\CCrmLiveFeedEvent::Message
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

			$res = \CCrmLead::getListEx(
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

			if (!empty($logEntry['PARAMS']))
			{
				$logEntry['PARAMS'] = unserialize($logEntry['PARAMS']);
				if (
					is_array($logEntry['PARAMS'])
					&& !empty($fields['CURRENT_ENTITY'])
				) // non-message
				{
					$this->setSourceTitle($fields['CURRENT_ENTITY']['TITLE']);
					$fields = array_merge($fields, $logEntry['PARAMS']);

					$sourceDescription = \Bitrix\Crm\Integration\Socialnetwork::buildAuxTaskDescription(
						$logEntry['PARAMS'],
						\Bitrix\Crm\Integration\Socialnetwork::DATA_ENTITY_TYPE_CRM_LEAD
					);

					if (!empty($sourceDescription))
					{
						$this->setSourceDescription(Loc::getMessage('CRMINTEGRATION_SONETLF_ENTITY_DESCRIPTION', array(
							'#LOGENTRY_TITLE#' => $logEntry['TITLE'],
							'#ENTITY_TITLE#' => $sourceDescription
						)));
					}
				}
				elseif ($logEntry['EVENT_ID'] == 'crm_lead_message')
				{
					$this->setSourceDescription($logEntry['MESSAGE']);
					$this->setSourceTitle(truncateText(($logEntry['TITLE'] != '__EMPTY__' ? $logEntry['TITLE'] : $logEntry['MESSAGE']), 100));
				}
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