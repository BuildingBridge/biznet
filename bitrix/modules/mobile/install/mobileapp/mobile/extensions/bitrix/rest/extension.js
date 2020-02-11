(() =>
{
	class RequestExecutor
	{
		constructor(method, options)
		{
			this.method = method;
			this.options = options;
			this.currentAnswer = null;
			this.handler = null;
			/**
			 *
			 * @type {function}
			 */
			this.onCacheFetched = null;
			this.cacheId = null;
		}

		call(useCache = false)
		{
			this.abortCurrentRequest();
			this.currentAnswer = null;
			if (this.onCacheFetched && useCache)
			{
				if (!this.cacheId)
				{
					this.cacheId = Object.toMD5(this.options) + "/" + this.method;
				}
				let cache = Application.storage.getObject(this.cacheId, []);
				this.onCacheFetched(cache);
			}

			BX.rest.callMethod(this.method, this.options, null, this.onRequestCreate.bind(this))
				.then(res =>
				{
					let result = res.answer.result;
					if (this.cacheId && res.answer.error == null)
					{
						Application.storage.setObject(this.cacheId, result)
					}
					return this.__internalHandler(res, false);
				})
				.catch(res => this.__internalHandler(res, false));
		}

		callNext()
		{
			if (this.hasNext())
			{
				this.abortCurrentRequest();
				this.currentAnswer.next()
					.then((res) => this.__internalHandler(res, true))
					.catch((res) => this.__internalHandler(res, true));
			}
		}

		abortCurrentRequest()
		{
			if (this.currentAjaxObject != null)
			{
				this.currentAjaxObject.abort();
			}
		}

		onRequestCreate(ajax)
		{
			this.currentAjaxObject = ajax;
		}

		hasNext()
		{
			return (this.currentAnswer != null && typeof this.currentAnswer.answer.next != "undefined");
		}

		getNextCount()
		{
			if (this.hasNext())
			{
				let countLeft = this.currentAnswer.answer.total - this.currentAnswer.answer.next;
				return countLeft > 50 ? 50 : countLeft;
			}

			return null;
		}

		getNext()
		{
			if (this.hasNext())
			{
				return this.currentAnswer.answer.next;
			}

			return null;
		}

		/**
		 *
		 * @param ajaxAnswer
		 * @param loadMore
		 * @private
		 */
		__internalHandler(ajaxAnswer, loadMore)
		{
			let result = ajaxAnswer.answer.result;
			let error;
			if (ajaxAnswer.answer.error)
			{
				error = {code: ajaxAnswer.answer.error, description: ajaxAnswer.answer.error_decription};
			}
			this.currentAnswer = ajaxAnswer;

			if (typeof this.handler == "function")
			{
				this.handler(result, loadMore, error)
			}
		}

		/**
		 *
		 * @param {function<object>} func
		 * @returns {RequestExecutor}
		 */
		setHandler(func)
		{
			this.handler = func;
			return this;
		}
		/**
		 *
		 * @param {function<object>} func
		 * @returns {RequestExecutor}
		 */
		setCacheHandler(func)
		{
			this.onCacheFetched = func;
			return this;
		}

		/**
		 *
		 * @param {String} id
		 */
		setCacheId(id)
		{
			this.cacheId = id;
			return this;
		}
	}

	/**
	 *  @interface DelayedRestRequestDelegate
	 * */
	/** Result method.
	 * @name  DelayedRestRequestDelegate#onDelayedRequestResult
	 * @param {{success:boolean}} result
	 * @type {Function}
	 * @return {void}
	 */
	/** Method gets params.
	 * @name  DelayedRestRequestDelegate#getParams
	 * @type {Function}
	 * @return {object}
	 */

	/**
	 * Class for delayed rest request
	 * @class DelayedRestRequest
	 */
	class DelayedRestRequest
	{
		/**
		 *
		 * @param method
		 * @param {DelayedRestRequestDelegate, null} delegate
		 */
		constructor(method, delegate = null)
		{
			if(delegate == null || typeof delegate !== "function")
				new Error("Argument (2) must be defined");

			this.method = method;
			this.timeoutId = 0;
			this.delay = 500;
			this._delegate = delegate;
		}

		get delegate()
		{
			return this._delegate;
		}

		send()
		{
			if (this.timeoutId)
			{
				clearTimeout(this.timeoutId);
			}

			this.timeoutId = setTimeout(
				()=>{
					BX.rest.callMethod(this.method, this.delegate.getParams())
						.then(() => this.delegate.onDelayedRequestResult({success: true}))
						.catch(() =>
						{
							return this.delegate.onDelayedRequestResult({success: false});
						})
				},
				this.delay
			);
		}
	}

	/**
	 *
	 * @param actionName
	 * @param params
	 * @return {Promise}
	 */

	/**
	 *
	 * @param {string} action
	 * @param {Object} config
	 * @param {?string|?Object} [config.analyticsLabel]
	 * @param {string} [config.method='POST']
	 * @param {Object} [config.data]
	 * @param {?Object} [config.getParameters]
	 * @param {?Object} [config.headers]
	 * @param {?Object} [config.timeout]
	 * @param {Object} [config.navigation]
	 * @param {number} [config.navigation.page]
	 * @param {function} [config.onCreate]
	 */
	BX.ajax.runAction = (action, config = {} )=>{
		let getParameters = prepareAjaxGetParameters(config);
		getParameters.action = action;
		let onCreate = (typeof config["onCreate"] == "function"? config["onCreate"]: ()=>{});
		let url = '/bitrix/services/main/ajax.php?' + BX.ajax.prepareData(getParameters);
		config = {
			url: url,
			method:"POST",
			dataType:"json",
			data: config.data,
		};
		let ajaxPromise = BX.ajax(config);
		onCreate(config.xhr);
		return ajaxPromise;
	};


	let prepareAjaxGetParameters = function(config)
	{
		let getParameters = config.getParameters || {};
		if (typeof config.analyticsLabel == "string")
		{
			getParameters.analyticsLabel = config.analyticsLabel;
		}
		else if (typeof config.analyticsLabel == "object")
		{
			getParameters.analyticsLabel = config.analyticsLabel;
		}
		if (typeof config.mode !== 'undefined')
		{
			getParameters.mode = config.mode;
		}
		if (config.navigation)
		{
			if(config.navigation.page)
			{
				getParameters.nav = 'page-' + config.navigation.page;
			}
			if(config.navigation.size)
			{
				if(getParameters.nav)
				{
					getParameters.nav += '-';
				}
				else
				{
					getParameters.nav = '';
				}
				getParameters.nav += 'size-' + config.navigation.size;
			}
		}

		return getParameters;
	};

	class RunActionExecutor
	{
		constructor(action, options)
		{
			this.action = action;
			this.options = options || {};
			this.currentAnswer = null;
			this.handler = null;
			/**
			 *
			 * @type {function}
			 */
			this.onCacheFetched = null;
			this.cacheId = null;
		}

		call(useCache = false)
		{
			this.abortCurrentRequest();
			this.currentAnswer = null;
			if (this.onCacheFetched && useCache)
			{
				if (!this.cacheId)
				{
					this.cacheId = Object.toMD5(this.options) + "/" + this.method;
				}
				let cache = Application.storage.getObject(this.cacheId, []);
				this.onCacheFetched(cache);
			}

			BX.ajax.runAction(this.action, {data:this.options, onCreate:this.onRequestCreate.bind(this)})
				.then(res =>
				{
					if (this.cacheId && res.error.length === 0)
					{
						Application.storage.setObject(this.cacheId, result)
					}
					return this.__internalHandler(res, false);
				})
				.catch(res => this.__internalHandler(res, false));
		}

		abortCurrentRequest()
		{
			if (this.currentAjaxObject != null)
			{
				this.currentAjaxObject.abort();
			}
		}

		onRequestCreate(ajax)
		{
			this.currentAjaxObject = ajax;
		}

		/**
		 *
		 * @param ajaxAnswer
		 * @private
		 */
		__internalHandler(ajaxAnswer)
		{
			let result = ajaxAnswer.data;
			let errors = ajaxAnswer.errors;
			this.currentAnswer = ajaxAnswer;

			if (typeof this.handler == "function")
			{
				this.handler(result, errors)
			}
		}

		/**
		 *
		 * @param {function<object>} func
		 * @returns {RequestExecutor}
		 */
		setHandler(func)
		{
			this.handler = func;
			return this;
		}

		/**
		 *
		 * @param {function<object>} func
		 * @returns {RequestExecutor}
		 */
		setCacheHandler(func)
		{
			this.onCacheFetched = func;
			return this;
		}

		/**
		 *
		 * @param {String} id
		 */
		setCacheId(id)
		{
			this.cacheId = id;
			return this;
		}

		updateOptions(options = null)
		{
			if(options != null && typeof options == "object")
				this.options = Object.assign(this.options, options);

			return this;
		}
	}

	class RunActionDelayedExecutor extends RunActionExecutor
	{
		constructor(action, options)
		{
			super(action, options);
			this.timeoutId = null;
			this.timeout = 300;
		}

		call()
		{
			clearTimeout(this.timeoutId);
			this.timeoutId = setTimeout(() => super.call(), this.timeout)
		}
	}

	window.RunActionDelayedExecutor = RunActionDelayedExecutor;
	window.RequestExecutor = RequestExecutor;
	window.RunActionExecutor = RunActionExecutor;
	window.DelayedRestRequest = DelayedRestRequest;


})();
