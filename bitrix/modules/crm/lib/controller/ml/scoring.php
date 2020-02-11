<?php

namespace Bitrix\Crm\Controller\Ml;

use Bitrix\Crm\Ml\ViewHelper;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;

class Scoring extends Controller
{
	/**
	 * Tries to create first prediction for the given entity.
	 *
	 * @param string $entityType String type of the entity.
	 * @param int $entityId Id of the entity
	 * @return array|null
	 */
	public function tryCreateFirstPredictionAction($entityType, $entityId)
	{
		$entityTypeId = \CCrmOwnerType::ResolveID($entityType);

		if(!Loader::includeModule("ml"))
		{
			$this->addError(new Error("ML module is not installed"));
			return null;
		}

		if(!\Bitrix\Crm\Ml\Scoring::isMlAvailable() || !\Bitrix\Crm\Ml\Scoring::isEnabled())
		{
			$this->addError(new Error("Scoring is not available for this portal"));
			return null;
		}

		$model = \Bitrix\Crm\Ml\Scoring::getScoringModel($entityTypeId, $entityId);
		if(!$model || $model->getState() !== \Bitrix\Ml\Model::STATE_READY)
		{
			$this->addError(new Error("Scoring model is not in ready state"));
			return null;
		}

		if(ViewHelper::isEntityFinal($entityTypeId, $entityId))
		{
			$this->addError(new Error("Entity is in final state"));
			return null;
		}

		$isPredictionCreated = \Bitrix\Crm\Ml\Scoring::tryCreateFirstPrediction($entityTypeId, $entityId, true);
		$currentPrediction = \Bitrix\Crm\Ml\Scoring::getCurrentPrediction($entityTypeId, $entityId);

		return [
			'predictionCreated' => $isPredictionCreated,
			'currentPrediction' => $currentPrediction
		];
	}
}