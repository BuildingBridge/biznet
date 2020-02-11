BX.namespace("BX.Crm");

if(typeof(BX.Crm.OrderShipment) === "undefined")
{
	BX.Crm.OrderShipment = function()
	{
		this._id = "";
		this._settings = {};
		this._editorCreateHandler = BX.delegate(this.onEditorCreate, this);
		this._editorUpdateHandler = BX.delegate(this.onEntityUpdate, this);
		this._editorDeleteHandler = BX.delegate(this.onSliderDelete, this);
	};
	BX.Crm.OrderShipment.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this.bindEvents();
		},
		bindEvents: function()
		{
			BX.addCustomEvent(window, BX.Crm.EntityEvent.names.create, this._editorCreateHandler);
			BX.addCustomEvent(window, BX.Crm.EntityEvent.names.update, this._editorUpdateHandler);
			BX.addCustomEvent(window, BX.Crm.EntityEvent.names.delete, this._editorDeleteHandler);
		},
		onEditorCreate: function(fields)
		{
			if (BX.type.isPlainObject(fields['entityData']))
			{
				var eventData = {
					entityTypeId: BX.CrmEntityType.enumeration.ordershipment,
					field: fields['entityData']
				};
				window.top.BX.SidePanel.Instance.postMessage(
					window,
					'CrmOrderShipment::Create',
					eventData
				);
			}
		},
		onEntityUpdate: function(fields)
		{
			if (BX.type.isPlainObject(fields['entityData']))
			{
				var eventData = {
					entityTypeId: BX.CrmEntityType.enumeration.ordershipment,
					field: fields['entityData']
				};
				window.top.BX.SidePanel.Instance.postMessage(
					window,
					'CrmOrderShipment::Update',
					eventData
				);
			}
		},
		onSliderDelete: function(fields)
		{
			if (parseInt(fields['id']) > 0)
			{
				var eventData = {
					entityTypeId: BX.CrmEntityType.enumeration.ordershipment,
					ID: fields['id']
				};
				window.top.BX.SidePanel.Instance.postMessage(
					window,
					'CrmOrderShipment::Delete',
					eventData
				);
			}
		}
	};
	BX.Crm.OrderShipment.create = function(id, settings)
	{
		var self = new BX.Crm.OrderShipment();
		self.initialize(id, settings);
		return self;
	};
}