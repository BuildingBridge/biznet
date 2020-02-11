<?php

namespace Bitrix\Crm\Order;

use Bitrix\Crm\Binding\OrderDealTable;
use Bitrix\Crm\EntityManageFacility;
use Bitrix\Crm\Integrity\ActualEntitySelector;
use Bitrix\Crm\Settings\DealSettings;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Crm\Timeline;

class DealBinding
{
	const OPTION_BIND_DEAL_TO_ORDER_NAME = 'bind_deal_to_order';

	protected $dealId;
	protected $order;
	protected $isNewDeal = false;
	protected $selector;
	protected $changedDealId;
	protected $isChanged = false;

	public function __construct(Order $order)
	{
		$this->order = $order;
		$this->dealId = $this->getDealId();
	}

	/**
	 * @return bool
	 */
	public static function isEnabled()
	{
		return Option::get('crm', static::OPTION_BIND_DEAL_TO_ORDER_NAME, 'Y') === 'Y';
	}

	/**
	 * @return bool
	 */
	public function isExist()
	{
		$dealId = $this->getDealId();
		return ($dealId && $dealId > 0);
	}

	/**
	 * @return false|int
	 */
	public function getDealId()
	{
		if($this->dealId === null)
		{
			$this->dealId = (int)$this->loadActualDealId();
		}

		return $this->dealId;
	}

	/**
	 * @return false|int
	 */
	protected function loadActualDealId()
	{
		return OrderDealTable::getDealIdByOrderId($this->order->getId());
	}

	/**
	 * @param $dealId
	 * @return $this
	 */
	public function setDealId($dealId)
	{
		$dealId = (int)$dealId;
		if($dealId > 0)
		{
			$this->changedDealId = $dealId;
		}
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isChanged()
	{
		return ($this->dealId > 0 && $this->changedDealId > 0 && $this->dealId != $this->changedDealId);
	}

	/**
	 * @return \Bitrix\Sale\Result
	 */
	public function create()
	{
		$result = new \Bitrix\Sale\Result();

		if(!static::isEnabled())
		{
			return $result;
		}

		if($this->isExist())
		{
			return $result->addError(new Error('Deal is already exist'));
		}

		$dealId = $this->searchActualDeal();
		if($dealId > 0)
		{
			$this->dealId = $dealId;
		}

		return $result;
	}

	private function searchActualDeal()
	{
		$selector = $this->getActualEntitySelector();
		$dealId = $selector->search()->getDealId();
		if ($dealId <= 0)
		{
			$dealData = OrderDealTable::getList([
				'select' => ['DEAL_ID'],
				'filter' => [
					'ORDER.USER_ID' => $this->order->getUserId(),
					'DEAL.CLOSED' => 'N'
				],
				'limit' => 1,
				'order' => ['DEAL_ID' => 'DESC']
			])->fetch();
			$dealId = (int)$dealData['DEAL_ID'];
		}

		return $dealId;
	}

	/**
	 * @return \Bitrix\Sale\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function save()
	{
		$result = new \Bitrix\Sale\Result();

		if($this->dealId > 0)
		{
			if($this->isChanged())
			{
				$this->dealId = $this->changedDealId;
				OrderDealTable::deleteByOrderId($this->order->getId());
				$this->isChanged = true;
			}
			else
			{
				return $result;
			}
		}

		if($this->dealId > 0)
		{
			$fields = $this->getDealFieldsOnUpdate();
			if(!empty($fields))
			{
				$deal = new \CCrmDeal();
				$deal->update($this->dealId, $fields);
			}
		}
		elseif(DealSettings::getCurrent()->isCreateDealOnOrderEnabled())
		{
			$selector = $this->getActualEntitySelector();

			$facility = new EntityManageFacility($selector);
			$facility->setDirection(EntityManageFacility::DIRECTION_OUTGOING);

			$fields = $this->getDealFieldsOnCreate();
			$dealId = $facility->registerDeal($fields);
			if(!$dealId)
			{
				foreach($facility->getErrorMessages() as $errorMessage)
				{
					$result->addError(new Error($errorMessage));
				}
			}
			else
			{
				$this->isNewDeal = true;
				$this->dealId = $dealId;
			}
		}

		if($this->dealId > 0)
		{
			$addResult = OrderDealTable::add([
				'ORDER_ID' => $this->order->getId(),
				'DEAL_ID' => $this->dealId,
			]);
			if(!$addResult->isSuccess())
			{
				$result->addErrors($addResult->getErrors());
			}
		}
		elseif(DealSettings::getCurrent()->isCreateDealOnOrderEnabled())
		{
			$result->addError(new Error('Could not create deal'));
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	protected function isNewDeal()
	{
		return $this->isNewDeal;
	}

	public function addTimelineCheckEntry($checkId, array $settings = [])
	{
		if ((int)$checkId <= 0)
		{
			return;
		}

		Timeline\OrderCheckEntry::create([
			'ENTITY_ID' => (int)$checkId,
			'AUTHOR_ID' => (int)$this->order->getField('RESPONSIBLE_ID'),
			'SETTINGS' => $settings,
			'BINDINGS' => [
				['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
			],
		]);
	}
	
	/**
	 * @return bool
	 */
	public function onAfterOrderSave()
	{
		if(!static::isEnabled() && ($this->getDealId() && !$this->isChanged))
		{
			return false;
		}
		$authorId = (int)$this->order->getField('RESPONSIBLE_ID');
		if ($this->order->isNew())
		{
			Timeline\OrderEntry::create([
				'ENTITY_ID' => $this->order->getId(),
				'TYPE_CATEGORY_ID' => Timeline\TimelineType::CREATION,
				'AUTHOR_ID' => $authorId,
				'SETTINGS' => [
					'FIELDS' => [
						'PAID' => $this->order->getField('PAYED'),
						'DONE' => ($this->order->getField('STATUS_ID') === OrderStatus::getFinalStatus()) ? 'Y' : 'N',
						'CANCELED' => $this->order->getField('CANCELED'),
					]
				],
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
				],
			]);

			Timeline\LinkEntry::create([
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Order,
				'ENTITY_ID' => $this->order->getId(),
				'AUTHOR_ID' => $authorId,
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
				]
			]);

			if ($this->isNewDeal())
			{
				Timeline\ConversionEntry::create([
					'ENTITY_TYPE_ID' => \CCrmOwnerType::Order,
					'ENTITY_ID' => $this->order->getId(),
					'AUTHOR_ID' => $authorId,
					'SETTINGS' => [
						'ENTITIES' => [
							[
								'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
								'ENTITY_ID' => $this->dealId
							]
						]
					]
				]);
			}
			else
			{
				Timeline\LinkEntry::create([
					'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
					'ENTITY_ID' => $this->getDealId(),
					'AUTHOR_ID' => $authorId,
					'BINDINGS' => [
						['ENTITY_TYPE_ID' => \CCrmOwnerType::Order,	'ENTITY_ID' => $this->order->getId()]
					]
				]);
			}
		}
		elseif ($this->order->getFields()->isChanged('CANCELED') && $this->order->getField('CANCELED') === 'Y')
		{
			Timeline\OrderEntry::create([
				'ENTITY_ID' => $this->order->getId(),
				'TYPE_CATEGORY_ID' => Timeline\TimelineType::MODIFICATION,
				'AUTHOR_ID' => $authorId,
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
				],
				'SETTINGS' => [
					'CHANGED_ENTITY' => \CCrmOwnerType::OrderName,
					'FIELDS' => [
						'ORDER_CANCELED' => $this->order->getField('CANCELED'),
					],
				]
			]);
		}
		elseif (
			$this->order->getPaymentCollection()->isChanged()
			|| (
				$this->order->getFields()->isChanged('STATUS_ID')
				&& $this->order->getField('STATUS_ID') === OrderStatus::getFinalStatus()
			)
		)
		{
			$paymentCollection = $this->order->getPaymentCollection();
			$paymentFields = [];
			$isNeedToAddTimeline = ($this->order->getField('STATUS_ID') === OrderStatus::getFinalStatus());
			/** @var \Bitrix\Sale\Payment $payment */
			foreach ($paymentCollection as $payment)
			{
				if ($payment->getFields()->isChanged('PAID'))
				{
					$isNeedToAddTimeline = true;
				}
				$paymentFields[$payment->getId()] = [
					'ID' => $payment->getId(),
					'PAID' => $payment->getField('PAID')
				];
			}

			if (!$isNeedToAddTimeline)
			{
				return true;
			}

			$entity = ($this->order->getField('STATUS_ID') === OrderStatus::getFinalStatus()) ? \CCrmOwnerType::OrderName : \CCrmOwnerType::OrderPaymentName;
			Timeline\OrderEntry::create([
				'ENTITY_ID' => $this->order->getId(),
				'TYPE_CATEGORY_ID' => Timeline\TimelineType::MODIFICATION,
				'AUTHOR_ID' => $authorId,
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
				],
				'SETTINGS' => [
					'CHANGED_ENTITY' => $entity,
					'FIELDS' => [
						'ORDER_PAID' => $this->order->getField('PAYED'),
						'ORDER_DONE' => ($this->order->getField('STATUS_ID') === OrderStatus::getFinalStatus()) ? 'Y' : 'N',
						'PAYMENTS_FIELDS' => $paymentFields
					],
				]
			]);
		}
		elseif ($this->order->getShipmentCollection()->isChanged())
		{
			$shipmentFields = [];
			$shipmentCollection = $this->order->getShipmentCollection();
			$isNeedToAddTimeline = false;
			/** @var \Bitrix\Sale\Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				if ($shipment->getFields()->isChanged('DEDUCTED'))
				{
					$isNeedToAddTimeline = true;
				}
				$shipmentFields[$shipment->getId()] = [
					'ID' => $shipment->getId(),
					'DEDUCTED' => $shipment->getField('DEDUCTED')
				];
			}

			if (!$isNeedToAddTimeline)
			{
				return true;
			}

			Timeline\OrderEntry::create([
				'ENTITY_ID' => $this->order->getId(),
				'TYPE_CATEGORY_ID' => Timeline\TimelineType::MODIFICATION,
				'AUTHOR_ID' => $authorId,
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
				],
				'SETTINGS' => [
					'CHANGED_ENTITY' => \CCrmOwnerType::OrderShipmentName,
					'FIELDS' => [
						'ORDER_DEDUCTED' => $this->order->getField('DEDUCTED'),
						'SHIPMENTS_FIELDS' => $shipmentFields
					],
				]
			]);
		}

		if($this->isChanged)
		{
			$this->isChanged = false;

			Timeline\LinkEntry::create([
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Order,
				'ENTITY_ID' => $this->order->getId(),
				'AUTHOR_ID' => $authorId,
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,	'ENTITY_ID' => $this->getDealId()]
				]
			]);

			Timeline\LinkEntry::create([
				'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
				'ENTITY_ID' => $this->getDealId(),
				'AUTHOR_ID' => $authorId,
				'BINDINGS' => [
					['ENTITY_TYPE_ID' => \CCrmOwnerType::Order,	'ENTITY_ID' => $this->order->getId()]
				]
			]);
		}

		return true;
	}

	/**
	 * @return ActualEntitySelector
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getActualEntitySelector()
	{
		if($this->selector === null)
		{
			$selector = new ActualEntitySelector();
			$contactCompanyCollection = $this->order->getContactCompanyCollection();
			/** @var ContactCompanyEntity $item */
			foreach($contactCompanyCollection as $item)
			{
				$selector->setEntity($item->getEntityType(), $item->getField('ENTITY_ID'));
			}
			$selector->setEntity(\CCrmOwnerType::Order, $this->order->getId());

			$this->selector = $selector;
		}

		return $this->selector;
	}

	/**
	 * @return array
	 */
	protected function getDealFieldsOnCreate()
	{
		$contactIds = [];
		$companyId = null;
		$contactId = null;
		$contactCompanyCollection = $this->order->getContactCompanyCollection();
		$company = $contactCompanyCollection->getPrimaryCompany();
		if($company)
		{
			$companyId = $company->getField('ENTITY_ID');
		}
		foreach($contactCompanyCollection->getContacts() as $contact)
		{
			/** @var Contact $contact */
			if($contact->isPrimary())
			{
				$contactId = $contact->getField('ENTITY_ID');
			}
			$contactIds[] = $contact->getField('ENTITY_ID');
		}
		return [
			'OPPORTUNITY' => $this->order->getPrice(),
			'CURRENCY_ID' => $this->order->getCurrency(),
			'ASSIGNED_BY_ID' => $this->order->getField('RESPONSIBLE_ID'),
			'CREATED_BY_ID' => $this->order->getField('RESPONSIBLE_ID'),
			'CONTACT_IDS' => $contactIds,
			'CONTACT_ID' => $contactId,
			'COMPANY_ID' => $companyId,
		];
	}

	/**
	 * @return bool
	 */
	protected function isUpdateDealOpportunityOnNewOrder()
	{
		return true;
	}

	/**
	 * @return array
	 */
	protected function getDealFieldsOnUpdate()
	{
		$fields = [];

		if($this->isUpdateDealOpportunityOnNewOrder())
		{
			$fields['OPPORTUNITY'] = $this->order->getPrice();
			$fields['CURRENCY_ID'] = $this->order->getCurrency();
		}

		return $fields;
	}
}