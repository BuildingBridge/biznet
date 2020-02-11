;(function () {

	if (!window.b24form)
	{
		window.b24form = function (data) {
			b24form.forms = b24form.forms || [];
			b24form.forms.push(data);
			if (data.ref && b24form.Loader && !this.loaded)
			{
				this.loaded = true;
				b24form.Loader.loadJs(data.ref, true);
			}
		};
	}

	if (window.b24form.Loader)
	{
		return;
	}

	function Loader ()
	{
		this.requested = false;
		this.queue = [];
	}
	Loader.prototype = {
		run: function (options)
		{
			options = options || {};
			var res = options.resources || {};

			this.queue.push(options.form);
			var loadForms = this.loadForms.bind(this);

			if (!this.requested)
			{
				this.requested = true;
				if (res.polyfill && !this.checkPolyfills())
				{
					this.loadJs(res.polyfill, true, loadForms);
				}
				if (res.babelHelpers && !this.checkBabelHelpers())
				{
					this.loadJs(res.babelHelpers, true, loadForms);
				}
				if (res.app && !window.b24form.App)
				{
					this.loadJs(res.app, true, loadForms);
				}
			}

			loadForms();
		},
		loadForms: function ()
		{
			if (!this.checkBabelHelpers() || !this.checkPolyfills())
			{
				return;
			}

			if (!window.b24form.App)
			{
				return;
			}

			var queue = this.queue;
			this.queue = [];
			queue.forEach(this.loadForm, this);
		},
		loadForm: function (form)
		{
			b24form.App.initFormScript24(form);
		},
		checkBabelHelpers: function ()
		{
			return window.babelHelpers;
		},
		checkPolyfills: function ()
		{
			return window.fetch && window.Request && window.Response
				&& window.Promise
				&& Object.assign
				&& Array.prototype.find && Array.prototype.includes
		},
		loadJs: function (content, isUrl, callback)
		{
			var node = document.createElement('SCRIPT');
			node.setAttribute("type", "text/javascript");
			node.setAttribute("async", "");

			if (isUrl)
			{
				node.setAttribute("src", content + '?' + (Date.now()/86400000|0));
				if (callback)
				{
					node.onload = callback;
				}
				this.appendToHead(node);
			}
			else
			{
				node.appendChild(document.createTextNode(content));
				this.appendToHead(node);
			}
		},
		appendToHead: function (node)
		{
			(document.getElementsByTagName('head')[0] || document.documentElement).appendChild(node);
		}
	};

	window.b24form.Loader = new Loader();
})();