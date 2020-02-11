/**
 * @bxjs_lang_path extension.php
 */

(() =>
{
	class TaskView
	{
		/**
		 *
		 * @param data
		 */
		static open(data)
		{
			data.canOpenInDefault = data.canOpenInDefault || false;
			console.log('TaskView.open');
			PageManager.openComponent("JSStackComponent",
				{
					canOpenInDefault: data.canOpenInDefault,
					scriptPath: data.path,
					componentCode: "tasks.list",
					params: data.params,
					rootWidget: {
						name: "tasks.list",
						settings: {
							objectName: "list",
							filter: "view_all",
							useSearch: true,
							menuSections: [{id: "presets"}, {id: "counters", itemTextColor: "#f00"}],
							menuItems: [
								{
									'id': "view_all",
									'title': BX.message("TASKS_ROLE_VIEW_ALL"),
									'sectionCode': 'presets',
									'showAsTitle': true,
									'badgeCount': 0
								},
								{
									'id': "view_role_accomplice",
									'title': BX.message("TASKS_ROLE_ACCOMPLICE"),
									'sectionCode': 'presets',
									'showAsTitle': true,
									'badgeCount': 0
								},
								{
									'id': "view_role_auditor",
									'title': BX.message("TASKS_ROLE_AUDITOR"),
									'sectionCode': 'presets',
									'showAsTitle': true,
									'badgeCount': 0
								},
								{

									'id': "view_role_originator",
									'title': BX.message("TASKS_ROLE_ORIGINATOR"),
									'sectionCode': 'presets',
									'showAsTitle': true,
									'badgeCount': 0
								},

							]
						},
					}
				}
			);
			console.log('TaskView.open:ed');
		}
	}

	class Task
	{
		constructor(currentUser)
		{
			this._id = 'tmp-id-' + (new Date).getTime();
			this.isNewRecord = true;
			this._guid = '';

			this.currentUser = currentUser;

			this.taskUrlTemplate = BX.componentParameters.get('PATH_TO_TASK_ADD',
				env.siteDir + "mobile/tasks/snmrouter/?routePage=#action#&TASK_ID=#taskId#");
			this.error = false;

			this._accomplices = [];
			this._auditors = [];

			this.deadline = null;
			this.timeTracking = false;

			this.status = 2;
			this.subStatus = this.status;
			this.notViewed = false;

			this.messageCount = 0;
			this.commentsCount = 0;
			this._newCommentsCount = 0;

			this.params = {};
			this.params.allowChangeDeadline = true;

			this.rawAccess = {};
		}

		setData(row)
		{
			this.id = row.id;
			this.title = row.title;
			this.groupId = row.groupId;
			this.group = (this.groupId > 0 && row.group) ? row.group : {name:'', id:0};

			this.status = row.status;
			this.subStatus = row['subStatus'] ? row.subStatus : this.status;

			this.creator = row.creator;
			this.responsible = row.responsible;

			this.timeTracking = row.allowTimeTracking === 'Y';

			this.commentsCount = row.commentsCount;
			this.newCommentsCount = row.newCommentsCount;

			this.notViewed = row.notViewed === 'Y';

			this.accomplices = row.accomplices || {};
			this.auditors = row.auditors || {};

			this.rawAccess = row.action;

			const deadline = Date.parse(row.deadline);
			if (deadline > 0)
			{
				this.deadline = deadline;
			}
			else
			{
				this.deadline = null;
			}

			if (Number(this.currentUser.id) === Number(row.creator.id)
				&& this.currentUser.icon !== row.creator.icon)
			{
				this.currentUser.icon = row.creator.icon;
			}

			if (Number(this.currentUser.id) === Number(row.responsible.id)
				&& this.currentUser.icon !== row.responsible.icon)
			{
				this.currentUser.icon = row.responsible.icon;
			}
		}

		cloneData(data)
		{
			for (let key in data)
			{
				if (this.hasOwnProperty(key))
				{
					this[key] = data[key];
				}
			}

		}

		static get statusList()
		{
			return {
				pending: 2,
				inprogress: 3,
				waitCtrl: 4,
				completed: 5,
				deferred: 6
			}
		}

		static get counterColors()
		{
			return {
				isNew: "#FF5752",//"#2FC6F6",
				expired: "#FF5752",
				waitCtrl: "#FF5752",//"#FFA900"
			};
		}

		isMember(userId)
		{
			userId = Number(userId);

			return userId === this.responsible.id ||
				userId === this.creator.id ||
				this.accomplices.indexOf(userId) >= 0 ||
				this.auditors.indexOf(userId) >= 0
		}

		get id()
		{
			return this._id.toString();
		}

		set id(id)
		{
			this._id = id.toString();
			this.isNewRecord = false;
		}

		get status()
		{
			return this._status;
		}

		set status(status)
		{
			this._status = Number(status);
		}

		get newCommentsCount()
		{
			if (isNaN(this._newCommentsCount) || typeof this._newCommentsCount == "undefined")
			{
				return 0;
			}

			return this._newCommentsCount;
		}

		set newCommentsCount(value)
		{
			if (value == null)
			{
				return;
			}

			if (isNaN(value) || typeof value == "undefined")
			{
				this._newCommentsCount = 0;
			}
			else
			{
				this._newCommentsCount = value;
			}
		}

		getMessageCount()
		{
			let count = 0;

			if (this.isCompleted)
			{
				return 0;
			}

			if (this.status > Task.statusList.waitCtrl)
			{
				return count;
			}

			if (this.isWaitCtrl) // wait ctrl
			{
				count++;
				return count;
			}

			if (this.isExpired) // expired
			{
				count++;
			}

			if (!this.isExpired && this.isExpiredSoon && this.auditors.indexOf(Number(this.currentUser.id)) < 0) // expiredsoon without auditors
			{
				count++;
			}

			if (this.isWoDeadline && this.auditors.indexOf(this.currentUser.id) < 0) // wo deadline
			{
				count++;
			}

			if (this.isNew && (this.currentUser.id !== this.creator.id || this.accomplices.indexOf(
				Number(this.currentUser.id)) >= 0) && this.auditors.indexOf(Number(this.currentUser.id)) < 0)
			{
				count++;
			}

			return count;
		}

		get isNew()
		{
			return Number(this.subStatus) === -2 || Number(this.subStatus) === 1 || this.notViewed;
		}

		get isWaitCtrl()
		{
			return this.status === Task.statusList.waitCtrl;
		}

		get isExpired()
		{
			const date = new Date;

			return this.deadline && this.deadline <= date.getTime() && !this.isWaitCtrl;
		}

		get isExpiredSoon()
		{
			const date = new Date;
			date.setDate(date.getDate() + 1);

			return this.deadline &&
				this.deadline <= date.getTime()
				&& !this.isWaitCtrl
				&& (
					this.auditors.indexOf(userId) < 0
					&&
					(
						this.creator.id !== this.currentUser.id
						|| (this.creator.id === this.currentUser.id && this.responsible.id === this.currentUser.id)
					)
				);
		}

		get isWoDeadline()
		{
			return !this.deadline &&
				(
					(this.responsible.id !== this.creator.id && this.responsible.id === this.currentUser.id)
					|| (this.responsible.id !== this.creator.id && this.creator.id === this.currentUser.id)
				);
		}

		get state()
		{
			return this.status === Task.statusList.inprogress ? BX.message('TASKS_LIST_STATE_INPROGRESS') : '';
		}

		get isCompleted()
		{
			if ((Number(this.status) === Task.statusList.waitCtrl && Number(this.currentUser.id) === Number(
				this.creator.id)))
			{
				return false;
			}

			return Number(this.status) === Task.statusList.completed ||
				(
					(
						Number(this.currentUser.id) === Number(this.responsible.id)
						||
						this.accomplices.indexOf(this.currentUser.id)
					) &&
					Number(this.status) === Task.statusList.waitCtrl
				);
		}

		get can()
		{
			return {
				changeDeadline:
					(this.rawAccess && this.rawAccess.changeDeadline)
				// || this.currentUser.isAdmin
				// || (this.currentUser.id === this.responsible.id && this.params.allowChangeDeadline)
				// || this.currentUser.id === this.creator.id
				// || (this.accomplices.indexOf(Number(this.currentUser.id)) >= 0 && this.params.allowChangeDeadline)
				,

				changeResponsible:
					(this.rawAccess && this.rawAccess.edit)
					|| this.currentUser.isAdmin
					// || this.currentUser.id === this.responsible.id
					|| this.currentUser.id === this.creator.id,

				start:
					(this.rawAccess && this.rawAccess.start),
				// || this.status === Task.statusList.pending &&
				// (
				// 	this.currentUser.id === this.responsible.id
				// 	// || this.currentUser.id === this.creator.id
				// 	|| this.accomplices.indexOf(Number(this.currentUser.id)) >= 0
				// ),

				pause:
					(this.rawAccess && this.rawAccess.pause),
				// || this.status === Task.statusList.inprogress &&
				// (
				// 	this.currentUser.id === this.responsible.id
				// 	// || this.currentUser.id === this.creator.id
				// 	|| this.accomplices.indexOf(Number(this.currentUser.id)) >= 0
				// ),

				complete:
					(this.rawAccess && this.rawAccess.complete),
				// || this.currentUser.isAdmin
				// || (
				// 	(this.status === Task.statusList.pending || this.status === Task.statusList.inprogress)
				// 	&& (
				// 		this.currentUser.id === this.responsible.id
				// 		|| this.currentUser.id === this.creator.id
				// 		|| this.accomplices.indexOf(Number(this.currentUser.id)) >= 0
				// 	)
				// ),

				renew:
					(this.rawAccess && this.rawAccess.renew),
				// || this.currentUser.isAdmin
				// || (
				// 	(this.status === Task.statusList.completed || this.status === Task.statusList.waitCtrl)
				// 	&& (
				// 		this.currentUser.id === this.responsible.id
				// 		|| this.currentUser.id === this.creator.id
				// 		|| this.accomplices.indexOf(Number(this.currentUser.id)) >= 0
				// 	)
				// ),

				update:
					(this.rawAccess && this.rawAccess.edit),
				// || this.currentUser.isAdmin
				// || this.currentUser.id === this.creator.id,

				remove:
					(this.rawAccess && this.rawAccess.remove),
				// || this.currentUser.isAdmin
				// || this.currentUser.id === this.creator.id,

				defer: (this.rawAccess && this.rawAccess.defer),
				disapprove: (this.rawAccess && this.rawAccess.disapprove),
				approve: (this.rawAccess && this.rawAccess.approve),
				delegate: (this.rawAccess && this.rawAccess.delegate),
				decline: (this.rawAccess && this.rawAccess.decline),
				'favorite.add': (this.rawAccess && this.rawAccess['favorite.add']),
				'favorite.remove': (this.rawAccess && this.rawAccess['favorite.delete'])
			};
		}

		set accomplices(values)
		{
			this._accomplices = [];

			if (values.length)
			{
				values.forEach(item =>
				{
					this._accomplices.push(Number(item));
				});
			}
		}

		get accomplices()
		{
			return this._accomplices;
		}

		set auditors(values)
		{
			this._auditors = [];

			if (values.length)
			{
				values.forEach(item =>
				{
					this._auditors.push(Number(item));
				});
			}
		}

		get auditors()
		{
			return this._auditors;
		}

		get guid()
		{
			function s4()
			{
				return Math.floor((1 + Math.random()) * 0x10000)
					.toString(16)
					.substring(1);
			}

			if (!this._guid)
			{
				this._guid = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
			}

			return this._guid;
		}

		static set guid(value)
		{
			this._guid = value;
		}

		save()
		{
			return new Promise((resolve, reject) =>
			{
				const request = new Request;

				if (this.isNewRecord)
				{
					console.log('Create task');
					request
						.call('add', {
							fields: {
								TITLE: this.title,
								CREATED_BY: this.creator.id,
								RESPONSIBLE_ID: this.responsible.id,
								STATUS: this.status,
								DEADLINE: this.deadline,
								GUID: this.guid,
								ALLOW_CHANGE_DEADLINE: 'Y',
								TASK_CONTROL: 'Y'
							}
						})
						.then(
							response =>
							{
								this.id = response.result.task.id;
								this.isNewRecord = false;
								this.error = false;
								this.rawAccess = response.result.task.action;

								resolve();
							},
							response =>
							{
								Log.error(response.ex.error_description);
								this.error = true;
								this.rawAccess = response.result.task.action;

								reject();
							}
						);
				}
				else
				{
					console.log('Update task');

					request
						.call('update', {
							taskId: this.id,
							fields: {
								TITLE: this.title,
								RESPONSIBLE_ID: this.responsible.id,
								STATUS: this.status,
								DEADLINE: this.deadline ? (new Date(this.deadline)).toISOString() : null,
							}
						})
						.then(
							response =>
							{
								this.error = false;
								console.log(response);
								resolve(response);
							},
							response =>
							{
								Log.error(response);
								this.error = true;

								reject(response);
							}
						);
				}
			});
		}

		saveDeadline()
		{
			return new Promise((resolve, reject) =>
			{
				const request = new Request;

				request
					.call('update', {
						taskId: this.id,
						fields: {
							DEADLINE: this.deadline ? (new Date(this.deadline)).toISOString() : null,
						}
					})
					.then(
						response =>
						{
							this.error = false;
							console.log(response);
							resolve(response);
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject(response);
						}
					);
			});
		}

		complete()
		{
			this.status = Task.statusList.completed;

			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('complete', {
						taskId: this.id,
						params: {HIDE: false}
					})
					.then(
						response =>
						{
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve();
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject();
						}
					);
			});
		}

		renew()
		{
			this.status = Task.statusList.pending;

			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('renew', {
						taskId: this.id,
						params: {HIDE: false}
					})
					.then(
						response =>
						{
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve();
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject();
						}
					);
			});
		}

		start()
		{
			this.status = Task.statusList.inprogress;

			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('start', {
						taskId: this.id,
					})
					.then(
						response =>
						{
							console.log(response.result.task);
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve();
						},
						response =>
						{
							Log.error(response);
							this.error = true;
							reject();
						}
					);
			});
		}

		pause()
		{
			this.status = Task.statusList.pending;

			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('pause', {
						taskId: this.id,
					})
					.then(
						response =>
						{

							console.log(response.result.task);
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve();
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject();
						}
					);
			});
		}

		approve()
		{
			this.status = Task.statusList.complete;

			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('approve', {
						taskId: this.id,
					})
					.then(
						response =>
						{
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve();
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject();
						}
					);
			});
		}

		disapprove()
		{
			this.status = Task.statusList.pending;

			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('disapprove', {
						taskId: this.id,
					})
					.then(
						response =>
						{
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve();
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject();
						}
					);
			});
		}

		favorite()
		{
			return {
				add: () =>
				{

					return new Promise((resolve, reject) =>
					{
						(new Request)
							.call('favorite.add', {
								taskId: this.id
							})
							.then(
								response =>
								{
									this.error = false;
									this.rawAccess['favorite.add'] = false;
									this.rawAccess['favorite.delete'] = true;
									resolve();
								},
								response =>
								{
									Log.error(response);
									this.error = true;

									reject();
								}
							);
					});
				},
				remove: () =>
				{
					return new Promise((resolve, reject) =>
					{
						const request = new Request;
						request
							.call('favorite.remove', {
								taskId: this.id
							})
							.then(
								response =>
								{
									this.error = false;
									this.rawAccess['favorite.add'] = true;
									this.rawAccess['favorite.delete'] = false;
									resolve();
								},
								response =>
								{
									Log.error(response);
									this.error = true;

									reject();
								}
							);
					});
				}

			};
		}

		stopWatch()
		{
			return new Promise((resolve, reject) =>
			{
				const request = new Request;
				request
					.call('stopWatch', {
						taskId: this.id,
					})
					.then(
						response =>
						{
							this.error = false;
							this.rawAccess = response.result.task.action;
							resolve(response);
						},
						response =>
						{
							Log.error(response);
							this.error = true;

							reject(response);
						}
					);
			});
		}

		open()
		{
			// TODO: uncomment after fix all bug in TASKS in branch
			//if (Application.getApiVersion() < 29)
			if (true)
			{
				PageManager.openPage({
					backdrop: {
						showOnTop: true,
						forceDismissOnSwipeDown: true
					},
					url: this.makeUrl(this.id),
					cache: false,
					modal: false,
					title: this.title ? this.title : null
				});
			}
			else
			{
				BX.postComponentEvent("taskbackground::task::action", [{taskId: this.id, title :this.title ? this.title : null}], "background");
				console.log('sendEvent to open task #'+this.id);
			}
		}

		makeUrl(taskId, action = 'view')
		{
			return this.taskUrlTemplate.replace('#action#', action).replace('#taskId#', taskId);
		}

	}

	//TODO duplicate from task.list
	class Request
	{
		constructor(namespace = 'tasks.task.')
		{
			this.restNamespace = namespace;
		}

		call(method, params)
		{
			this.currentAnswer = null;
			this.abortCurrentRequest();
			return new Promise((resolve, reject) =>
			{
				console.log({
					method: this.restNamespace + method,
					params: params
				});

				BX.rest.callMethod(this.restNamespace + method, params || {}, response =>
				{
					this.currentAnswer = response;

					if (response.error())
					{
						console.log(response.error());
						reject(response);
					}
					else
					{
						resolve(response.answer);
					}
				}, this.onRequestCreate.bind(this));
			});
		}

		onRequestCreate(ajax)
		{
			this.currentAjaxObject = ajax;
		}

		abortCurrentRequest()
		{
			if (this.currentAjaxObject != null)
			{
				this.currentAjaxObject.abort();
			}
		}
	}

	this.TaskView = TaskView;
	this.Task = Task;
})();