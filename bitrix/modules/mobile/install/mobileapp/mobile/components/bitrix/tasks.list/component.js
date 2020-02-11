"use strict";
/**
 * @bxjs_lang_path component.php
 */

include("InAppNotifier");

(function () {
    const pathToIcons = '/bitrix/mobileapp/mobile/components/bitrix/tasks.list/';

    const userId = parseInt(BX.componentParameters.get('USER_ID', 0));
    const debug = true;

    class App {
        constructor(list, userId) {
            Log.debug('app.init ' + Application.getApiVersion());
            this.list = list;

            this.currentUser = result.settings.userInfo;
            this.taskList = new TaskList(this.list, this.currentUser, {id: userId});

            const eventHandlers = {
                onRefresh: {
                    cb: () => {
                        this.taskList.reload(0, false);
                        this.taskList.filter.updateCounters();
                    },
                    ctx: this.taskList
                },
                onNewItem: {
                    cb: this.taskList.onNewItem,
                    ctx: this.taskList
                },
                onFilterItemSelected: {
                    cb: this.taskList.onFilterItemSelected,
                    ctx: this.taskList
                },
                onHideSubfilterPanel: {
                    cb: this.taskList.onHideSubfilterPanel,
                    ctx: this.taskList
                },
                onUserTypeText: {
                    cb: this.taskList.onUserTypeText,
                    ctx: this.taskList
                },
                onSearchItemSelected: {
                    cb: this.taskList.onSearchItemSelected,
                    ctx: this.taskList
                },
                onSearchShow: {
                    cb: this.taskList.onSearchShow,
                    ctx: this.taskList
                },
                onSearchHide: {
                    cb: this.taskList.onSearchHide,
                    ctx: this.taskList
                },
                onItemSelected: {
                    cb: this.taskList.onItemSelected,
                    ctx: this.taskList
                },
                onItemAction: {
                    cb: this.taskList.onItemAction,
                    ctx: this.taskList
                },
                onItemChecked: {
                    cb: this.taskList.onItemChecked,
                    ctx: this.taskList
                },
                onSectionFolded: {
                    cb: SectionsInternal.onFolded,
                    ctx: this.taskList
                }
            };

            this.list.setListener((event, data) => {
                Log.debug('Fire event: app.' + event);
                if (eventHandlers[event]) {
                    eventHandlers[event].cb.apply(
                        eventHandlers[event].ctx,
                        [data]
                    );
                }
            });
        }

        static showError(message = null, title = "") {
            if (message == null)
                return;

            if (PageManager.getNavigator().isVisible() && PageManager.getNavigator().isActiveTab())//visibility check
            {
                InAppNotifier.showNotification({
                    message: message,
                    title: title,
                    backgroundColor: "#333333"
                });
            }
        }

        static resolveErrorMessageByAnswer(answer = null) {
            let messageError = BX.message("TASKS_SOME_THING_WENT_WRONG");
            if (answer && answer.ex) {
                switch (answer.ex.status) {
                    case -2:
                        messageError = BX.message("TASKS_NO_INTERNET_CONNECTION");
                        break;
                    case 0:
                        messageError = BX.message("TASKS_UNEXPECTED_ANSWER");
                        break;
                    default:
                        messageError = BX.message("TASKS_SOME_THING_WENT_WRONG");
                        break;
                }
            }

            console.log('Error');
            console.log(answer);
            return messageError;
        }
    }

    class TaskList {
        constructor(list, currentUser, owner) {
            Log.debug('taskList.constructor');

            this.tasksCache = new Cache('tasks.task.list' + owner.id);
            this.tasksSearchCache = new Cache('tasks.task.search' + owner.id);

            this.groupId = parseInt(BX.componentParameters.get('GROUP_ID', 0));

            this.list = list;
            /**
             * @type {Map<String, Task>}
             */
            this.taskList = new Map;
            this.searchTaskList = new Map();
            this.dontUpdateTask = new Map(); // registry for check onpulladd
            this.hiddenTasks = new Map();
            this.currentUser = currentUser;
            this.owner = owner;

            this.snackBarHideCompleted = true;
            this.minApiVersion = 30;
            this.showGroups = false; //0 && Application.getApiVersion() >= this.minApiVersion;

            this.filter = new Filter(this.list, this.currentUser, this.owner, this.groupId);

            this.start = 0;
            this.total = 0;

            this.order = new Order(this.currentUser.id, this.owner.id);

            this.titleLoaderShowTimeout = null;

            this.menu = this.list.popupMenu;
            this.popupMenuInit();

            this.flagChangeRole = false;

            this.pullInit();

            this.fnUserTypeText = TaskList.debounce((event) => {
                console.log('fnUserTypeText');
                this.list.setSearchResultItems([
                    {
                        type: 'loading',
                        title: BX.message('TASKS_LIST_BUTTON_NEXT_PROCESS'),
                        sectionCode: "default",
                    }
                ], [{id: "default"}]);

                let filter = {
                    SEARCH_INDEX: event.text,
                    MEMBER: this.currentUser.id
                };

                if (!this.filter.showCompleted) {
                    filter['::SUBFILTER-STATUS-OR'] = {};
                    filter['::SUBFILTER-STATUS-OR']['::LOGIC'] = 'OR';
                    filter['::SUBFILTER-STATUS-OR']['::SUBFILTER-1'] = {
                        'REAL_STATUS': [2, 3]
                    };
                    filter['::SUBFILTER-STATUS-OR']['::SUBFILTER-2'] = {
                        'REAL_STATUS': [4],
                        'CREATED_BY': this.currentUser.id
                    };
                }

                this.searchRequest.call('list', {
                    select: TaskList._selectFields,
                    filter,
                    order: {
                        'group_id': 'asc',
                        'status_complete': "asc"
                    },
                    params: {
                        RETURN_ACCESS: 'Y',
                        RETURN_USER_INFO: 'Y',
                        RETURN_GROUP_INFO: 'Y',
                        SEND_PULL: 'Y'
                }
                })
                    .then(
                        (response, next, total) => {

                            this.searchTaskList.clear();

                            if (response.result.tasks.length) {
                                response.result.tasks.forEach(row => {
                                    let task = {};

                                    if (this.taskList.has(row.id.toString())) {
                                        task = this.taskList.get(row.id.toString());
                                    } else {
                                        task = new Task(this.currentUser);
                                        task.setData(row);

                                        this.taskList.set(task.id, task);
                                    }

                                    this.searchTaskList.set(task.id, task);
                                });
                            }

                            this.fillSearchCache(response.result.tasks);

                            this.searchListRender();
                        },
                        response => {
                            this.debug('Error task search');
                            this.debug(response);
                            this.error(response.ex.error_description);
                        }
                    );

            }, 1000, this);

            BX.onViewLoaded(() => {
                this.list.setItems([
                    {
                        type: 'loading',
                        title: BX.message('TASKS_LIST_BUTTON_NEXT_PROCESS')
                    }
                ]);

                this.loadTasksFromCache();
                this.reload();

            });
        }

        loadTasksFromSearchCache() {
            Log.debug('LoadFromCache: search');
            BX.onViewLoaded(() => {
                const result = this.tasksSearchCache.get();
                const dataCache = result['last'] ? result['last'] : {};

                if (!dataCache || Object.keys(dataCache).length === 0) {
                    Log.debug('search cache empty... exit!');
                    return false;
                }

                dataCache.map(
                    row => {
                        let task = {};

                        if (this.taskList.has(row.id.toString())) {
                            task = this.taskList.get(row.id.toString());
                        } else {
                            task = new Task(this.currentUser);
                            task.setData(row);

                            this.taskList.set(task.id, task);
                        }

                        this.searchTaskList.set(task.id, task);
                    }
                );

                this.searchListRender();
            });
            return true;
        }


        static get statusList() {
            return Task.statusList;
        }

        static get counterColors() {
            return Task.counterColors;
        }

        pullInit() {
            BX.addCustomEvent('task.view.onCommentsRead',
                (data) => {
                    console.log('task.view.onCommentsRead', data);
                    let taskId = String(data.taskId);

                    if (this.taskList.has(taskId)) {

                        const task = this.taskList.get(taskId);
                        task.newCommentsCount = 0;

                        this.updateItem(taskId, {newCommentsCount: ''});
                    }
                    else
                    {
                        console.log('task not found', taskId);
                    }
                });

            const handlers = {
                task_add: this.onPullAdd,
                task_update: this.onPullUpdate,
                task_remove: this.onPullDelete,
                comment_add: this.onPullComment,
                user_counter: this.filter.onUserCounter
            };

            BX.addCustomEvent("onPullEvent-tasks",
                (command, params, extra) => {
                    if (handlers[command]) {
                        handlers[command].apply(this, [params]);
                    }
                });

            // const handlersComment = {
            // 	comment: this.onPullComment,
            // 	comment_mobile: this.onPullComment,
            // };
            //
            // BX.addCustomEvent("onPullEvent-unicomments",
            // 	(command, params, extra) => {
            // 		Log.debug({command: command, params: params, extra: extra});
            // 		if(handlersComment[command])
            // 		{
            // 			handlersComment[command].apply(this, [params]);
            // 		}
            // 	});

            const fn = TaskList.debounce(() => {
                //this.filter.updateCounters();
            }, 1000);
            const handlersCounters = {
                user_counter: fn
            };

            BX.addCustomEvent("onPullEvent-main",
                (command, params, extra) => {
                    Log.debug({command: command, params: params, extra: extra});
                    if (handlersCounters[command]) {
                        handlersCounters[command].apply(this, [params]);
                    }
                });
        }

        onPullComment(data) {
            // Log.debug('onPullComment');
            const xmlId = data.ENTITY_XML_ID.split('_');
            if (xmlId[0] !== 'TASK') {
                Log.debug('Not task comment');
                return false;
            }

            if (Number(data.OWNER_ID) === Number(this.currentUser.id)) {
                return false;
            }

            const taskId = xmlId[1];
            if (this.taskList.has(taskId)) {
                const task = this.taskList.get(taskId);
                task.newCommentsCount = task.newCommentsCount + 1;
                const count = task.newCommentsCount;

                this.updateItem(taskId, {newCommentsCount: count});
                // Log.debug({newCommentsCount:task.newCommentsCount});
            }
        }

        onPullAdd(data) {
            Log.debug('onPullAdd');
            const taskId = data.TASK_ID.toString();

            if (!this.taskList.has(taskId)) {


                if (this.dontUpdateTask.has(taskId)) {
                    this.dontUpdateTask.delete(taskId);
                    return;
                }

                const request = new Request();
                request.call('get', {taskId: taskId}).then(
                    response => {
                        const row = response.result.task;
                        const task = new Task(this.currentUser);

                        if (this.dontUpdateTask.has(row.guid)) {
                            this.dontUpdateTask.delete(row.guid);
                            return;
                        }

                        task.setData(row);

                        this.addItem(task);
                    },
                    response => {
                        Log.debug(response);
                    }
                );
            }
        }

        onPullDelete(data) {
            Log.debug('onPullDelete');
            if (this.searchTaskList.has(data.TASK_ID.toString())) {
                this.searchTaskList.delete(data.TASK_ID.toString());
                this.searchListRender();
            }

            this.removeItem(data.TASK_ID.toString())
        }

        onPullUpdate(data) {
            Log.debug({title: 'onPullUpdate', data});
            const taskId = data.TASK_ID.toString();

            const request = new Request();
            request.call('get', {taskId: taskId, select: TaskList._selectFields}).then(
                response => {
                    const row = response.result.task;

                    // Log.debug('row');
                    // Log.debug(row);

                    if (this.taskList.has(taskId)) {
                        // Log.debug('has');
                        const task = this.taskList.get(taskId);
                        // Log.debug(task);

                        if (
                            !this.filter.showCompleted
                            && task.isCompleted
                            && (data.params && data.params.HIDE)
                        // && (
                        //     Number(row.status) === TaskList.statusList.completed
                        //     || Number(row.status) === TaskList.statusList.waitCtrl
                        // )
                        ) {
                            this.removeItem(taskId);
                        }

                        if (this.dontUpdateTask.has(row.id)) {
                            this.dontUpdateTask.delete(row.id);
                            return;
                        }

                        task.setData(row);


                        let sec = SectionsInternal.getInstance();
                        if (this.showGroups) {
                            sec.add({
                                title: (row.groupId > 0 ? row.group.name : ''),
                                id: 'section-' + row.groupId,
                                foldable: (row.groupId > 0),
                                folded: (row.groupId > 0),
                                //badgeValue: Number(item.messageCount)
                            });
                        }

                        this.updateItem(taskId, task);
                    } else {
                        const task = new Task(this.currentUser);

                        if (this.dontUpdateTask.has(row.id)) {
                            this.dontUpdateTask.delete(row.id);
                            return;
                        }

                        task.setData(row);

                        Log.debug('add');
                        this.addItem(task);
                    }
                },
                response => {
                    Log.debug('onPullUpdate.get.error');
                    Log.debug(response);
                }
            );

        }

        addItem(item) {
            this.addItems([item]);
        }

        addItems(items) {
            BX.onViewLoaded(() => {
                this.removeItem('-none-');
                let ar = [];

                items.forEach(
                    task => {
                        if (!this.taskList.has(task.id)) {
                            this.taskList.set(task.id, task);
                            ar.push(this.getItemDataFromTask(task));
                        }
                    }
                );

                this.list.prependItems(ar);
            });
        }

        getItemDataFromTask(task, withActions = true) {
            let item = {
                id: task.id,
                title: task.title,
                checked: task.isCompleted,
                deadline: task.deadline,
                state: task.state,
                messageCount: task.getMessageCount(),
                actions: []
            };

            item.newCommentsCount = task.newCommentsCount ? Number(task.newCommentsCount) : 0;

            item.creatorIcon = item.responsibleIcon = '';
            // if (task.responsible.id !== task.creator.id || (task.responsible.id === task.creator.id && task.creator.id !== this.currentUser.id)) {

            item.creatorIcon = task.creator.icon;
            item.responsibleIcon = task.responsible.icon;
            // }

            item.checkable = task.can.complete || task.can.renew;

            if(!this.showGroups)
                item.sectionCode = 'default';
            else
            {
                item.sectionCode = task.groupId > 0 ? 'section-' + task.groupId : 'default';
            }


            // item.indicator = task.indicator;

            if (withActions) {
                item.checkable = true;

                if (task.can.changeDeadline) {
                    item.actions.push({
                        identifier: 'changeDeadline',
                        title: BX.message('TASKS_LIST_ACTION_DEADLINE'),
                        iconName: 'term',
                        color: '#FF8F30',
                        destruct: false,
                        position: 'right'
                    });
                }

                if (task.can.changeResponsible) {
                    item.actions.push({
                        identifier: 'changeResponsible',
                        title: BX.message('TASKS_LIST_ACTION_RESPONSIBLE'),
                        iconName: 'userlist',
                        color: '#0064C1',
                        destruct: false,
                        position: 'right'
                    });
                }

                if (task.can.start) {
                    item.actions.push({
                        identifier: 'changeState',
                        title: BX.message('TASKS_LIST_ACTION_STATE_START'),
                        iconName: 'start',
                        color: '#00C7F3',
                        destruct: false,
                        position: 'right'
                    });
                }

                if (task.can.pause) {
                    item.actions.push({
                        identifier: 'changeState',
                        title: BX.message('TASKS_LIST_ACTION_STATE_PAUSE'),
                        iconName: 'finish',
                        color: '#00C7F3',
                        destruct: false,
                        position: 'right'
                    });
                }

                if (task.can.remove) {
                    item.actions.push({
                        identifier: 'remove',
                        title: BX.message('TASKS_LIST_ACTION_REMOVE'),
                        iconName: 'delete',
                        color: '#FF5B50',
                        // destruct: true,
                        position: 'right'
                    });
                }

                // if (task.can.complete && !item.checked) {
                //     item.actions.push({
                //         identifier: 'complete_toggle',
                //         title: BX.message('TASKS_LIST_ACTION_COMPLETE'),
                //         shouldToggleCheckmark: true,
                //         expandable: true,
                //         iconName: 'finish',
                //         color: '#2FC6F6',
                //         position: 'left'
                //     });
                // }

                if (!(task.can.complete || task.can.renew)) {
                    item.checkable = false;
                }

                if (item.actions.length < 4) {
                    if (task.auditors.indexOf(Number(this.currentUser.id)) >= 0) {
                        item.actions.push({
                            identifier: 'unfollow',
                            title: BX.message('TASKS_LIST_ACTION_DONT_FOLLOW'),
                            iconName: 'unfollow',
                            color: '#2FC6F6',
                            position: 'right'
                        });
                    }
                }
            }

            return item;
        }

        updateItem(id, fields) {
            BX.onViewLoaded(() => {
                const taskId = id.toString();

                if (!this.taskList.has(taskId)) {
                    return;
                }

                const task = this.taskList.get(taskId);

                if (fields.id && fields.id !== taskId) {
                    this.taskList.delete(taskId);
                    this.taskList.set(fields.id.toString(), task);
                }

                for (let key in fields) {
                    if (task.hasOwnProperty(key) || key === 'status') {
                        task[key] = fields[key];
                    }
                }

                fields = this.getItemDataFromTask(task);

                console.log('updateItem #' + id, fields);
                this.list.updateItem({id: id}, fields);
            });
        }

        removeItem(id) {
            BX.onViewLoaded(() => {
                this.taskList.delete(id);
                this.list.removeItem({id: id});
            });
        }

        onItemSelected(item) {
            if (item.id === '-more-') {
                this.list.updateItem({id: '-more-'}, {
                    type: 'loading',
                    title: BX.message('TASKS_LIST_BUTTON_NEXT_PROCESS')
                });
                this.reload(this.start);
            }
            if (item.id === '-none-') {
                this.reload(this.start);
            } else {
                const taskId = item.id.toString();

                if (this.taskList.has(taskId)) {
                    const task = this.taskList.get(item.id);

                    if (this.currentUser.id == this.owner.id) {
                        if (this.filter.currentSubFilterId === 'not_viewed') {
                            this.removeItem(item.id);
                        } else {
                            task.notViewed = false;

                            this.updateItem(item.id, {newCommentsCount: 0, notViewed: task.notViewed});
                        }
                    }

                    task.open();
                }
            }
        }

        onItemChecked(item) {
            const task = this.taskList.get(item.id.toString());

            this.dontUpdateTask.set(task.id, task);

            if (task.isCompleted) {
                task.status = Task.statusList.pending;
                this.updateItem(task.id, {newCommentsCount: 0});

                task.renew();
            } else {
                this.snackBarHideCompleted = true;
                this.showSnackbarCompleted();

                task.status = Task.statusList.completed;
                this.updateItem(task.id, {newCommentsCount: 0});
                task.complete();

            }

            const fn = () => this.filter.updateCounters();

            setTimeout(fn, 1000);
        }

        onNewItem(title) {
            Log.debug('onNewItem');
            const task = new Task(this.currentUser);
            this.dontUpdateTask.set(task.guid);

            task.title = title;
            task.creator = this.currentUser;
            task.responsible = this.currentUser;

            this.addItem(task);
            const oldTaskId = task.id;

            task.save().then(
                () => {
                    this.updateItem(oldTaskId, {id: task.id});
                },
                (e) => {
                    console.error(e)
                }
            );
        }

        onItemAction(event) {
            const task = this.taskList.get(event.item.id);
            const request = new Request();

            switch (event.action.identifier) {
                case "remove":

                    dialogs.showActionSheet({
                        title: BX.message('TASKS_CONFIRM_DELETE'),
                        "callback": item => {
                            if (item.code === 'YES')
                            {
                                this.removeItem(task.id);
                                this.filter.pseudoUpdateCounter(task, -1);
                                // this.showSnackbarRemoved();

                                request.call('delete', {taskId: event.item.id})
                                    .then(
                                        response =>
                                        {
                                        },
                                        response =>
                                        {
                                            Log.debug(response);
                                        }
                                    );
                            }
                        },
                        "items": [
                            {title: BX.message('TASKS_CONFIRM_DELETE_YES'), code: "YES"},
                            {title: BX.message('TASKS_CONFIRM_DELETE_NO'), code: "NO"},
                        ]
                    });
                    break;

                case "changeDeadline":
                    const oldTs = task.deadline;

                    dialogs.showDatePicker(
                        {
                            title: BX.message('TASKS_LIST_POPUP_SELECT_DATE'),
                            type: 'datetime',
                            value: task.deadline
                        },
                        (eventName, ts) => {
                            if (!ts) {
                                return;
                            }

                            if (ts > 0) {
                                this.updateItem(task.id, {deadline: ts});
                                this.filter.pseudoUpdateCounter(task, -1);
                            }

                            task.saveDeadline();
                        }
                    );
                    break;

                case 'changeResponsible':
                    const userList = new UserList(this.list, task.responsible, this.currentUser, {
                        onSelect: (items) => {
                            const user = items[0];

                            task.responsible = TaskList.getItemDataFromUser(user);

                            this.updateItem(task.id, {responsibleIcon: task.responsible.icon});
                            this.filter.pseudoUpdateCounter(task, -1);
                            task.save();
                        }
                    });
                    break;
                case 'changeState': {
                    if (task.status !== TaskList.statusList.inprogress) {
                        task.rawAccess.start = false;
                        task.rawAccess.pause = true;
                        task.start();
                    } else {

                        task.rawAccess.start = true;
                        task.rawAccess.pause = false;
                        task.pause();
                    }

                    this.updateItem(task.id, {status: task.status});
                    break;
                }

                case 'unfollow':
                    task.auditors = task.auditors.filter(e => e !== Number(this.currentUser.id)); // remove au from task object

                    if (!task.isMember(this.currentUser.id)) {
                        this.removeItem(task.id);
                    }

                    task.stopWatch();
                    break;
            }
        }

        static getItemDataFromUser(user) {
            return {
                id: user.id,
                name: user.title,
                icon: user.imageUrl,
                link: ''
            };
        }

        loadTasksFromCache() {
            Log.debug('LoadFromCache: ' + this.filter.cacheId);
            BX.onViewLoaded(() => {
                const result = this.tasksCache.get();
                const dataCache = result[this.filter.cacheId] ? result[this.filter.cacheId] : {};
                if (!dataCache || Object.keys(dataCache).length === 0) {
                    Log.debug('cache empty... exit!');
                    return false;
                }

                let items = dataCache.map(
                    row => {
                        const task = new Task(this.currentUser);
                        task.setData(row);

                        this.taskList.set(task.id, task);

                        return this.getItemDataFromTask(task);
                    }
                );

                this.list.setItems(items, null, true);
            });
        }

        static get _selectFields()
        {
            return [
                'ID',
                'TITLE',
                'STATUS',
                'CREATED_BY',
                'RESPONSIBLE_ID',
                'DEADLINE',
                'COMMENTS_COUNT',
                'AUDITORS',
                'ACCOMPLICES',
                'NEW_COMMENTS_COUNT',
                'FAVORITE',
                'NOT_VIEWED',
                'GROUP_ID'
            ];
        }

        reload(taskListOffset = 0, titleLoadingIndicator = true) {
            BX.onViewLoaded(() => {

                Log.debug('RELOAD');

                const request = new Request();
                this.hiddenTasks.clear();

                const filter = this.filter.get();
                let orderField = {};

                if (this.showGroups) {
                    orderField['GROUP_ID'] = this.order.direction;
                }

                orderField[this.order.field] = this.order.direction;
                orderField['ID'] = 'asc';

                if (titleLoadingIndicator == true)
                    this.showTitleLoader();

                request.call('list', {
                    select: TaskList._selectFields,
                    filter: filter,
                    order: orderField,
                    start: taskListOffset,
                    params: {
                        RETURN_ACCESS: 'Y',
                        RETURN_USER_INFO: 'Y',
                        RETURN_GROUP_INFO: 'Y',
                        SEND_PULL: 'Y'
                    }
                })
                    .then(response => {
                        if (titleLoadingIndicator)
                            this.showTitleLoader(false);

                        dialogs.hideLoadingIndicator();
                        let next = response.next;
                        let total = response.total;
                        let result = response.result;
                        this.list.removeItem({id: '-more-'});

                        this.start = next;
                        this.total = total;

                        if (taskListOffset === 0) {
                            this.fillCache(result.tasks);
                        }

                        let ar = [];
                        let sections = [];

                        let sec = SectionsInternal.getInstance();
                        if (taskListOffset === 0) {
                            console.log('sec.clear');
                            sec.clear();
                        }

                        result.tasks.forEach(row => {
                            const task = new Task(this.currentUser);
                            task.setData(row);
                            let item = this.getItemDataFromTask(task);

                            if (this.showGroups) {
                                sec.add({
                                    title: (row.groupId > 0 ? row.group.name : ''),
                                    id: 'section-' + row.groupId,
                                    foldable: (row.groupId > 0),
                                    folded: (row.groupId > 0),
                                    badgeValue: Number(item.messageCount)
                                });
                            }

                            ar.push(item);
                            this.taskList.set(task.id, task);
                        });

                        console.log('setSections');
                        console.log(sec.list);
                        console.log(sec.keys);

                        sec.remove('more-button');
                        sec.add({
                            title: '',
                            id: 'section-more-button',
                            foldable: false,
                            folded: false
                        });

                        this.list.setSections(sec.list);

                        if (ar.length > 0) {
                            if (Number(taskListOffset) === 0) {
                                this.list.setItems(ar, null, true);
                            } else {
                                this.list.addItems(ar);
                            }

                            if (next) {
                                this.list.addItems([{
                                    id: '-more-',
                                    title: BX.message('TASKS_LIST_BUTTON_NEXT'),
                                    type: 'button',
                                    sectionCode: 'section-more-button'
                                }]);
                            }
                        } else {
                            this.list.setItems([{
                                id: '-none-',
                                title: BX.message('TASKS_LIST_NOTHING_NOT_FOUND'),
                                type: 'button'
                            }]);
                        }
                        this.list.stopRefreshing();
                    })
                    .catch(response => {
                        this.onReloadError(response, titleLoadingIndicator);
                    });
            });
        }

        fillCache(list) {
            let dataCache = this.tasksCache.get();
            if (typeof dataCache != "object") {
                dataCache = {};
            }

            dataCache[this.filter.cacheId] = list;
            this.tasksCache.set(dataCache);

            Log.debug('Save to cache: ' + this.filter.cacheId);
        }

        fillSearchCache(list, sections) {
            let dataCache = this.tasksSearchCache.get();
            if (typeof dataCache != "object") {
                dataCache = {};
            }

            dataCache['last'] = list;
            dataCache['sections'] = sections;
            this.tasksSearchCache.set(dataCache);

            Log.debug('Save to search cache');
        }

        onReloadError(response, titleLoadingIndicator) {
            setTimeout(() => {
                if (titleLoadingIndicator)
                    this.showTitleLoader(false);
                dialogs.hideLoadingIndicator();
                this.list.stopRefreshing();
                let messageError = App.resolveErrorMessageByAnswer(response.answer);

                App.showError(messageError, BX.message("TASKS_ERROR_TITLE"));
            }, 700); //to make smooth disappearing

            //TODO do something with the list
            Log.debug('Stop refreshing');
        }

        // filter
        onFilterItemSelected(filter) {
            this.filter.setCurrentFilterId(filter.id);
            this.loadTasksFromCache();
            this.reload();
        }

        onHideSubfilterPanel() {
            this.filter.clearSubFilter();
            this.reload();
        }

        // snackbar
        showSnackbarCompleted() {
            if (this.filter.showCompleted) {
                return;
            }
            const title = this.snackBarHideCompleted ? 'TASKS_SNACKBAR_HIDE_CLOSED_TASKS' : 'TASKS_SNACKBAR_SHOW_CLOSED_TASKS';

            dialogs.showSnackbar(
                {
                    title: BX.message(title),
                    autoHide: true,
                    showCloseButton: true,
                    backgroundColor: '#E3F8FF',
                    textColor: '525C69'
                },
                this.onSnackBarCompleted.bind(this)
            );
        }

        showTitleLoader(show = true) {
            clearTimeout(this.titleLoaderShowTimeout);
            if (typeof list.filterMenu.switchLoader != "undefined") {
                if (show) {
                    if (this.titleLoaderShowTimeout == null) {
                        this.titleLoaderShowTimeout = setTimeout(() => {
                            console.log("showTitleLoader show");

                            this.titleLoaderShowTimeout = null
                            list.filterMenu.switchLoader(show);
                        }, 200);
                    }
                } else {
                    console.log("showTitleLoader hide");
                    list.filterMenu.switchLoader(show)
                }
            }
        }

        onSnackBarCompleted(eventName) {
            switch (eventName) {
                case 'onClose':
                    if (!Application.sharedStorage().get('tasks.blink_completed')) {
                        this.menu.show([
                            {
                                id: 'toggleCompletedTasks',
                                'blink': true
                            }
                        ]);
                        Application.sharedStorage().set('tasks.blink_completed', 'true');
                    }
                    break;
                case 'onClick':
                    // this.popupMenuInit();

                    if (this.snackBarHideCompleted) {
                        this.hiddenTasks = new Map();
                        this.taskList.forEach(task => {
                            if (task.isCompleted) {
                                this.hiddenTasks.set(task.id, task);
                                this.removeItem(task.id);
                            }
                        });
                    } else {
                        this.addItems(this.hiddenTasks);
                        this.hiddenTasks.clear();
                    }

                    this.snackBarHideCompleted = !this.snackBarHideCompleted;

                    this.showSnackbarCompleted();
                    break;
            }
        }

        static pathToIcon(iconName = null) {
            if (iconName == null) {
                return null;
            }
            return pathToIcons + '/images/' + iconName;
        }

        // menu
        popupMenuInit() {
            let menu = [];

            menu.push({
                id: 'toggleCompletedTasks',
                title: BX.message('TASKS_POPUP_MENU_' + (!this.filter.showCompleted ? 'SHOW' : 'HIDE') + '_CLOSED_TASKS'),
                iconName: 'finished_tasks',
                sectionCode: 'default'
            });

            if(0 && Application.getApiVersion() >= this.minApiVersion)
            {
                menu.push({
                    id: 'toggleShowGroup',
                    title: BX.message('TASKS_POPUP_MENU_' + (!this.showGroups ? 'SHOW' : 'HIDE') + '_GROUPS'),
                    // iconName: 'finished_tasks',
                    sectionCode: 'default'
                });
            }

            menu.push({
                id: 'orderDirection',
                    title: BX.message('TASKS_POPUP_MENU_ORDER_DIRECTION_ASC'),
                iconUrl: this.order.direction === 'desc' ? TaskList.pathToIcon("check.png") : '',
                sectionCode: 'sortDirection'
            });

            menu.push({
                id: 'orderDirection',
                    title: BX.message('TASKS_POPUP_MENU_ORDER_DIRECTION_DESC'),
                iconUrl: this.order.direction === 'asc' ? TaskList.pathToIcon("check.png") : '',
                sectionCode: 'sortDirection'
            });

            menu.push({
                id: 'orderField',
                    title: BX.message('TASKS_POPUP_MENU_ORDER_FIELD_' + this.order.field.toUpperCase()),
                iconName: 'sort_field',
                sectionCode: 'sortField'
            });

            this.menu.setData(
                menu,
                [
                    {
                        id: 'default'
                    },
                    {
                        id: 'sortDirection',
                        title: BX.message('TASKS_POPUP_MENU_SECTION_HEADER_ORDER_DIRECTION'),
                    },
                    {
                        id: 'sortField',
                        title: BX.message('TASKS_POPUP_MENU_SECTION_HEADER_ORDER_FIELD'),
                    }
                ],
                (eventName, item) => {
                    if (eventName === 'onItemSelected') {
                        this.onPopupMenuClick(item);
                    }
                }
            );
        }

        onPopupMenuClick(item) {
            switch (item.id) {
                case 'toggleCompletedTasks':
                    this.filter.showCompleted = !this.filter.showCompleted;
                    this.popupMenuInit();
                    this.reload();
                    break;
                case 'toggleShowGroup':
                    this.showGroups = !this.showGroups;
                    this.popupMenuInit();
                    this.reload();
                    break;
                case 'orderDirection':
                    this.order.direction = this.order.direction === 'asc' ? 'desc' : 'asc';
                    this.popupMenuInit();
                    this.reload();
                    break;
                case 'orderField':
                    dialogs.showPicker(
                        {
                            title: BX.message('TASKS_POPUP_MENU_ORDER_FIELD'),
                            items: this.order.fields,
                            defaultValue: this.order.field
                        },
                        (event, item) => {
                            if (event === 'onPick') {
                                this.order.field = item.value;
                                this.popupMenuInit();
                                this.reload();
                            }
                        }
                    );
                    break;
            }
        }

        // search
        onUserTypeText(event) {
            console.log('onUserTypeText()');
            BX.onViewLoaded(() => {
                if (event.text === '' || event.text.length <= 3) {

                    this.list.setSearchResultItems([
                        {
                            type: 'button',
                            unselectable: true,
                            title: BX.message('TASKS_LIST_SEARCH_HINT'),
                            sectionCode: "default",
                        }
                    ], [{id: "default"}]);
                    return;
                }

                this.fnUserTypeText(event);
            });
        }

        searchListRender() {
            let ar = [];
            let sec = SectionsInternal.getInstance();
            sec.add({title: '', id: 'default', foldable: false, folded: false, badgeValue:0});
            sec.add({title: '', id: 'section-0', foldable: false, folded: false, badgeValue: 0});

            console.log('searchListRender');
            console.log(this.searchTaskList.size);
            console.log(this.searchTaskList.values());

            if (this.searchTaskList.size > 0) {
                for (let task of this.searchTaskList.values()) {

                    let item = this.getItemDataFromTask(task, false);
                    ar.push(item);

                    if (this.showGroups) {
                        sec.add({
                            title: (task.groupId > 0 ? task.group.name : ''),
                            id: 'section-' + task.groupId,
                            foldable: (task.groupId > 0),
                            folded: (task.groupId > 0),
                            badgeValue: 0
                        });
                    }
                }
            } else {
                console.log('Empty');
                ar.push({
                    id: 0,
                    title: BX.message('TASKS_LIST_SEARCH_EMPTY_RESULT'),
                    sectionCode: 'section-0',
                    type: 'button',
                    unselectable: true
                });
            }

            console.log({title: 'search', ar, sections:sec.list});
            this.list.setSearchResultItems(ar, sec.list);
        }

        static debounce(fn, timeout, ctx) {
            let timer = 0;
            ctx = ctx || this;

            return function () {
                let args = arguments;

                clearTimeout(timer);

                timer = setTimeout(function () {
                    fn.apply(ctx, args);
                }, timeout);
            }
        }

        onSearchShow() {
            this.searchRequest = new Request;

            if (!this.loadTasksFromSearchCache()) {
                if (!this.searchTaskList.keys().length) {
                    this.list.setSearchResultItems([
                        {
                            type: 'button',
                            unselectable: true,
                            title: BX.message('TASKS_LIST_SEARCH_HINT'),
                            sectionCode: "default",
                        }
                    ], [{id: "default"}]);
                }
            }
        }

        onSearchHide() {
            this.searchTaskList.clear();
        }

        onSearchItemSelected(event) {
            this.searchTaskList.get(event.id.toString()).open();
        }

    }

    class Request {
        constructor(namespace = 'tasks.task.') {
            this.restNamespace = namespace;
        }

        call(method, params) {
            this.currentAnswer = null;
            this.abortCurrentRequest();
            return new Promise((resolve, reject) => {
                Log.debug({
                    method: this.restNamespace + method,
                    params: params
                });

                BX.rest.callMethod(this.restNamespace + method, params || {}, response => {
                    this.currentAnswer = response;

                    if (response.error()) {
                        Log.debug(response.error());
                        reject(response);
                    } else {
                        resolve(response.answer);
                    }
                }, this.onRequestCreate.bind(this));
            });
        }


        onRequestCreate(ajax) {
            this.currentAjaxObject = ajax;
        }

        abortCurrentRequest() {
            if (this.currentAjaxObject != null) {
                this.currentAjaxObject.abort();
            }
        }
    }

    class Log {
        static debug(text) {
            // if (this.debug)
            {
                console.log(text);
            }
        }

        static error(text) {
            // todo!
            debug(text);
        }
    }

    class Filter {
        constructor(list, currentUser, owner, groupId) {

            this.groupId = groupId || 0;

            this.showZombie = false;
            this.currentUser = currentUser;
            this._currentFilterId = 'view_all';
            this._currentSubFilterId = '';
            this.list = list;
            this.owner = owner;
            this.filter = this.list.filterMenu;
            this.showCompleted = false;

            this.counters = {};

            this.sections = [
                {id: 'presets'},
                {
                    id: 'counters-blue',
                    itemTextColor: TaskList.counterColors.isNew,
                    badgeColor: TaskList.counterColors.isNew
                },
                {
                    id: 'counters-red',
                    itemTextColor: TaskList.counterColors.expired
                },
                {
                    id: 'counters-orange',
                    itemTextColor: TaskList.counterColors.waitCtrl,
                    badgeColor: TaskList.counterColors.waitCtrl,
                },
            ];
            this.presets = [
                {
                    id: "view_all",
                    title: BX.message('TASKS_ROLE_VIEW_ALL'),
                    sectionCode: 'presets',
                    showAsTitle: true,
                    badgeCount: 0
                },
                {
                    id: "view_role_responsible",
                    title: BX.message('TASKS_ROLE_RESPONSIBLE'),
                    sectionCode: 'presets',
                    showAsTitle: true,
                    badgeCount: 0
                },
                {
                    id: "view_role_accomplice",
                    title: BX.message('TASKS_ROLE_ACCOMPLICE'),
                    sectionCode: 'presets',
                    showAsTitle: true,
                    badgeCount: 0
                },
                {
                    id: "view_role_auditor",
                    title: BX.message('TASKS_ROLE_AUDITOR'),
                    sectionCode: 'presets',
                    showAsTitle: true,
                    badgeCount: 0
                },
                {
                    id: "view_role_originator",
                    title: BX.message('TASKS_ROLE_ORIGINATOR'),
                    sectionCode: 'presets',
                    showAsTitle: true,
                    badgeCount: 0
                }
            ];
            this.subPresets = [
                {
                    id: "wo_deadline",
                    title: BX.message('TASKS_COUNTER_WO_DEADLINE'),
                    sectionCode: 'counters-red',
                    showAsTitle: false,
                    badgeCount: 0
                },
                {
                    id: "expired",
                    title: BX.message('TASKS_COUNTER_EXPIRED'),
                    sectionCode: 'counters-red',
                    showAsTitle: false,
                    badgeCount: 0
                },
                {
                    id: "expired_soon",
                    title: BX.message('TASKS_COUNTER_EXPIRED_SOON'),
                    sectionCode: 'counters-red',
                    showAsTitle: false,
                    badgeCount: 0
                },
                {
                    id: "not_viewed",
                    title: BX.message('TASKS_COUNTER_NOT_VIEWED'),
                    sectionCode: 'counters-blue',
                    showAsTitle: false,
                    badgeCount: 0
                },
                {
                    id: "wait_ctrl",
                    title: BX.message('TASKS_COUNTER_WAIT_CTRL'),
                    sectionCode: 'counters-orange',
                    showAsTitle: false,
                    badgeCount: 0
                }
            ];

            this.filter.setSections(this.sections);
            this.filter.addItems(this.presets);

            this.cache = new Cache('tasks.list.filter.defaultFilter');
            let defaultFilter = this.cache.get();

            if (defaultFilter && defaultFilter.currentFilterId) {
                this._currentFilterId = defaultFilter.currentFilterId;
            }

            this.cacheCounters = new Cache('tasks.list.filter.counters');
            if (this.cacheCounters) {
                this.counters = this.cacheCounters.list;
            }

            this.updateCounters();
        }


        get() {
            let where = {
                ZOMBIE: this.showZombie ? 'Y' : 'N',
                CHECK_PERMISSIONS: 'Y'
            };

            where['MEMBER'] = this.owner.id;

            if(this.groupId > 0)
            {
                where['GROUP_ID'] = this.groupId;
            }

            if (!this.showCompleted) {
                where['::SUBFILTER-STATUS-OR'] = {};
                where['::SUBFILTER-STATUS-OR']['::LOGIC'] = 'OR';
                where['::SUBFILTER-STATUS-OR']['::SUBFILTER-1'] = {
                    'REAL_STATUS': [2, 3]
                };
                where['::SUBFILTER-STATUS-OR']['::SUBFILTER-2'] = {
                    'REAL_STATUS': [4],
                    'CREATED_BY': this.currentUser.id
                };

            } else {
                where['::SUBFILTER-STATUS-OR'] = {};
                where['::SUBFILTER-STATUS-OR']['::LOGIC'] = 'OR';
                where['::SUBFILTER-STATUS-OR']['::SUBFILTER-1'] = {
                    'REAL_STATUS': [2, 3, 5]
                };
                where['::SUBFILTER-STATUS-OR']['::SUBFILTER-2'] = {
                    'REAL_STATUS': [4],
                    // '!RESPONSIBLE_ID': this.currentUser.id,
                    'ROLE': 'O'
                };
            }

            switch (this.currentFilterId) {
                case 'view_all':
                    break;
                case 'view_role_responsible':
                    where['ROLE'] = 'R';
                    break;
                case 'view_role_accomplice':
                    where['ROLE'] = 'A';
                    where['!REAL_STATUS'] = 4;
                    break;
                case 'view_role_auditor':
                    where['ROLE'] = 'U';
                    where['!REAL_STATUS'] = 4;
                    break;
                case 'view_role_originator':
                    where['ROLE'] = 'O';
                    // where['REAL_STATUS']=4;
                    break;
            }

            if (this.currentSubFilterId) {

                const currentDate = (new Date);
                switch (this.currentSubFilterId) {
                    case 'expired':
                        where['!REAL_STATUS'] = 4;
                        where['<=DEADLINE'] = (new Date).toISOString();
                        break;
                    case 'expired_soon':
                        where['!REAL_STATUS'] = 4;
                        where['!AUDITOR'] = [this.currentUser.id];
                        const currentDate = new Date;
                        const expiredSoon = new Date;
                        expiredSoon.setDate(currentDate.getDate() + 1);

                        where['>=DEADLINE'] = currentDate.toISOString();
                        where['<=DEADLINE'] = expiredSoon.toISOString();
                        break;
                    case 'not_viewed':
                        where['!REAL_STATUS'] = 4;
                        where['NOT_VIEWED'] = 'Y';
                        break;
                    case 'wo_deadline':
                        where['!REAL_STATUS'] = 4;
                        // where['=DEADLINE'] = ''; //TODO
                        // where['!RESPONSIBLE_ID']=this.currentUser.id;
                        where['WO_DEADLINE'] = 'Y';

                        break;
                    case 'wait_ctrl':
                        where['REAL_STATUS'] = 4;
                        where['ROLE'] = 'O';
                        break;
                }
            }

            return where;

        }

        get cacheId() {
            return this.currentFilterId + this.currentSubFilterId;
        }

        get currentFilterId() {
            return this._currentFilterId;
        }

        setCurrentFilterId(id) {
            if (this._getCol(this.presets, 'id').indexOf(id) >= 0) {
                this._currentFilterId = id;
                this._currentSubFilterId = '';
                this.list.hideSubfilterPanel();

                this.cache.set({currentFilterId: id});
            }

            if (this._getCol(this.subPresets, 'id').indexOf(id) >= 0) {
                this._currentSubFilterId = id;

                let title = 'n/a';
                this.subPresets.forEach(function (item) {
                    if (item.id == id) {
                        title = item.title;
                    }
                });

                this.list.showSubfilterPanel({title: title});
            }

            this.updateCounters();
        }

        get currentSubFilterId() {
            return this._currentSubFilterId;
        }

        _getCol(matrix, col) {
            let column = [];
            for (let i = 0; i < matrix.length; i++) {
                column.push(matrix[i][col]);
            }
            return column;
        }

        clearSubFilter() {
            this._currentSubFilterId = '';
        }

        isSubfilterActive() {
            return (this.currentSubFilterId != "");
        }


        onUserCounter(data) {
            if (!data[this.currentFilterId]) {
                Log.debug({error: this.currentFilterId + 'not found in data', data});
            }

            const counter = data[this.currentFilterId];

            let countersData = [];
            this.presets.forEach((item) => {
                item.badgeCount = counter[item.id].total;

                countersData.push(item);

                if (item.id === this.currentFilterId) {
                    let subCounters = [];

                    if (counter.wo_deadline && Number(counter.wo_deadline) > 0) {
                        subCounters.push({
                            id: 'wo_deadline',
                            title: BX.message('TASKS_COUNTER_WO_DEADLINE'),
                            badgeCount: counter.wo_deadline,
                        });
                    }
                    if (counter.not_viewed && Number(counter.not_viewed) > 0) {
                        subCounters.push({
                            id: 'not_viewed',
                            title: BX.message('TASKS_COUNTER_NOT_VIEWED'),
                            badgeCount: counter.not_viewed
                        });
                    }
                    if (counter.expired && Number(counter.expired) > 0) {
                        subCounters.push({
                            id: 'expired',
                            title: BX.message('TASKS_COUNTER_EXPIRED'),
                            badgeCount: counter.expired,
                        });
                    }
                    if (counter.expired_soon && Number(counter.expired_soon) > 0) {
                        subCounters.push({
                            id: 'expired_soon',
                            title: BX.message('TASKS_COUNTER_EXPIRED_SOON'),
                            badgeCount: counter.expired_soon
                        });
                    }
                    if (counter.wait_ctrl && Number(counter.wait_ctrl) > 0) {
                        subCounters.push({
                            id: 'wait_ctrl',
                            title: BX.message('TASKS_COUNTER_WAIT_CTRL'),
                            badgeCount: currentCounter.wait_ctrl
                        });
                    }

                    subCounters = subCounters.map(item => {
                        item.showAsTitle = false;
                        item.textColor = "#707070";
                        item.sectionCode = 'presets';
                        item.type = "subitem";

                        return item;
                    });

                    if (subCounters.length > 0) {
                        subCounters.push({
                            id: 'space',
                            sectionCode: 'presets',
                            type: "space",
                        });
                    }

                    countersData = countersData.concat(subCounters);
                }
            });

            this.filter.setItems(countersData, null, this.currentFilterId);
            if (this.currentUser.id == this.owner.id) {
                Application.setBadges({tasks: this.counters.view_all_total});
            }
        }

        updateCounters() {
            Log.debug('UPDATE COUNTERS');

            const request = new Request;
            const restNamespace = request.restNamespace;

            BX.rest.callBatch({
                    view_all: [
                        restNamespace + 'counters.get',
                        {type: 'view_all', userId: this.owner.id}
                    ],
                    view_role_responsible: [
                        restNamespace + 'counters.get',
                        {type: 'view_role_responsible', userId: this.owner.id}
                    ],
                    view_role_accomplice: [
                        restNamespace + 'counters.get',
                        {type: 'view_role_accomplice', userId: this.owner.id}
                    ],
                    view_role_auditor: [
                        restNamespace + 'counters.get',
                        {type: 'view_role_auditor', userId: this.owner.id}
                    ],
                    view_role_originator: [
                        restNamespace + 'counters.get',
                        {type: 'view_role_originator', userId: this.owner.id}
                    ]
                },

                result => {
                    //region FILL COUNTERS
                    let countersData = [];

                    if (!result.view_all.answer.result) {
                        return;
                    }

                    //region total counters
                    this.counters = {
                        view_all: result.view_all.answer.result,
                        view_role_responsible: result.view_role_responsible.answer.result,
                        view_role_accomplice: result.view_role_accomplice.answer.result,
                        view_role_auditor: result.view_role_auditor.answer.result,
                        view_role_originator: result.view_role_originator.answer.result,
                    };

                    const view_all = this.counters.view_all;
                    this.counters.view_all_total =
                        (view_all.wo_deadline ? view_all.wo_deadline.counter : 0) +
                        (view_all.expired ? view_all.expired.counter : 0) +
                        (view_all.expired_soon ? view_all.expired_soon.counter : 0) +
                        (view_all.not_viewed ? view_all.not_viewed.counter : 0) +
                        (view_all.wait_ctrl ? view_all.wait_ctrl.counter : 0);

                    const view_role_responsible = this.counters.view_role_responsible;
                    this.counters.view_role_responsible_total =
                        (view_role_responsible.wo_deadline ? view_role_responsible.wo_deadline.counter : 0) +
                        (view_role_responsible.expired ? view_role_responsible.expired.counter : 0) +
                        (view_role_responsible.expired_soon ? view_role_responsible.expired_soon.counter : 0) +
                        (view_role_responsible.not_viewed ? view_role_responsible.not_viewed.counter : 0);

                    const view_role_accomplice = this.counters.view_role_accomplice;
                    this.counters.view_role_accomplice_total =
                        (view_role_accomplice.not_viewed ? view_role_accomplice.not_viewed.counter : 0) +
                        (view_role_accomplice.expired ? view_role_accomplice.expired.counter : 0) +
                        (view_role_accomplice.expired_soon ? view_role_accomplice.expired_soon.counter : 0);

                    const view_role_auditor = this.counters.view_role_auditor;
                    this.counters.view_role_auditor_total =
                        (view_role_auditor.expired ? view_role_auditor.expired.counter : 0)

                    const view_role_originator = this.counters.view_role_originator;
                    this.counters.view_role_originator_total =
                        (view_role_originator.wo_deadline ? view_role_originator.wo_deadline.counter : 0) +
                        (view_role_originator.expired ? view_role_originator.expired.counter : 0) +
                        (view_role_originator.wait_ctrl ? view_role_originator.wait_ctrl.counter : 0);
                    //endregion


                    this.presets.forEach((item) => {
                        let key = item.id + '_total';

                        // if(item.id === this.currentFilterId)
                        // {
                        //     item.badgeCount = 0;
                        // }
                        // else {
                        item.badgeCount = this.counters[key];
                        // }
                        countersData.push(item);

                        if (item.id === this.currentFilterId) {
                            const currentCounter = this.counters[this.currentFilterId];
                            let subCounters = [];
                            if (currentCounter) {
                                if (currentCounter.wo_deadline && Number(currentCounter.wo_deadline.counter) > 0) {
                                    subCounters.push({ //TODO dont dublicate this , use this.subPresets
                                        id: 'wo_deadline',
                                        title: BX.message('TASKS_COUNTER_WO_DEADLINE'),
                                        badgeCount: currentCounter.wo_deadline.counter,
                                        // badgeColor: TaskList.counterColors.expired
                                    });
                                }
                                if (currentCounter.not_viewed && Number(currentCounter.not_viewed.counter) > 0) {
                                    subCounters.push({
                                        id: 'not_viewed',
                                        title: BX.message('TASKS_COUNTER_NOT_VIEWED'),
                                        badgeCount: currentCounter.not_viewed.counter,
                                        // badgeColor: TaskList.counterColors.isNew
                                    });
                                }
                                if (currentCounter.expired && Number(currentCounter.expired.counter) > 0) {
                                    subCounters.push({
                                        id: 'expired',
                                        title: BX.message('TASKS_COUNTER_EXPIRED'),
                                        badgeCount: currentCounter.expired.counter,
                                        // badgeColor: TaskList.counterColors.expired
                                    });
                                }
                                if (currentCounter.expired_soon && Number(currentCounter.expired_soon.counter) > 0) {
                                    subCounters.push({
                                        id: 'expired_soon',
                                        title: BX.message('TASKS_COUNTER_EXPIRED_SOON'),
                                        badgeCount: currentCounter.expired_soon.counter,
                                        // badgeColor: TaskList.counterColors.expired
                                    });
                                }
                                if (currentCounter.wait_ctrl && Number(currentCounter.wait_ctrl.counter) > 0) {
                                    subCounters.push({
                                        id: 'wait_ctrl',
                                        title: BX.message('TASKS_COUNTER_WAIT_CTRL'),
                                        badgeCount: currentCounter.wait_ctrl.counter,
                                        // badgeColor: TaskList.counterColors.waitCtrl
                                    });
                                }

                                subCounters = subCounters.map(item => {
                                    item.showAsTitle = false;
                                    item.textColor = "#707070";
                                    item.sectionCode = 'presets';
                                    item.type = "subitem";
                                    return item;
                                });

                                if (subCounters.length > 0) {
                                    subCounters.push({
                                        id: 'space',
                                        sectionCode: 'presets',
                                        type: "space",
                                    });
                                }

                                countersData = countersData.concat(subCounters);
                            }
                        }
                    });

                    this.filter.setItems(countersData, null, this.currentFilterId);
                    if (this.currentUser.id == this.owner.id) {
                        Application.setBadges({tasks: this.counters.view_all_total});
                    }
                });
        }

        pseudoUpdateCounter(task, value) {
            let data = [
                {id: 'view_all', value: (this.counters.view_all_total + value)},
                {id: this.currentFilterId, value: (this.counters[this.currentFilterId] + value)}
            ];

            let downCounters = [];
            if (task.isExpired) {
                downCounters.push('expired');
            }
            if (task.isExpiredSoon) {
                downCounters.push('expired_soon');
            }
            if (task.isNew) {
                downCounters.push('not_viewed');
            }
            if (task.isWoDeadline) {
                downCounters.push('wo_deadline');
            }

            downCounters.forEach(counterId => {
                if (this.counters[counterId]) {
                    data.push({id: counterId, value: (this.counters[counterId] + value)});
                }
            });

            this.filter.updateItems(data);
        }
    }

    class UserList {
        constructor(list, responsible, currentUser, handlers) {
            this.list = list;
            this.responsible = responsible;

            this.currentUser = currentUser;
            this.handlers = handlers;

            this.cache = new Cache('tasks.user.list2');
            this.request = new Request('user.');
            this.users = new Map;
            this.usersSort = [];

            if (!this.loadUsersFromCache()) {
                this.loadUsersFromComponent();
            }

            this.onUserTypeText = TaskList.debounce((event) => {
                if (event.text.length <= 2) {
                    this.loadUsersFromCache();
                    this.list.setItems(this.getList());
                    return;
                }

                this.list.setItems([{type: 'loading', title: BX.message('TASKS_LIST_BUTTON_NEXT_PROCESS')}]);

                this.request.call('search', {
                    IMAGE_RESIZE: "small",
                    SORT: "LAST_NAME",
                    ORDER: "ASC",
                    FILTER: {
                        ACTIVE: "Y",
                        NAME_SEARCH: event.text
                    }
                }).then(
                    response => {
                        let userList = [];

                        if (response.result.length) {

                            userList = response.result.map(
                                item => {
                                    return {
                                        id: item.ID,
                                        title: UserList.getFormattedName(item),
                                        imageUrl: encodeURI(item.PERSONAL_PHOTO),
                                        color: "#5D5C67",
                                        useLetterImage: true,
                                        sectionCode: 'default'
                                    }
                                }
                            );

                        } else {
                            userList.push({
                                id: 0,
                                title: BX.message('TASKS_LIST_SEARCH_EMPTY_RESULT'),
                                sectionCode: 'default',
                                type: 'button',
                                unselectable: true
                            });
                        }

                        this.list.setItems(userList);
                    },

                    response => {
                        Log.error(response);
                    }
                );
            }, 1000, this);

            list.showUserList(
                {
                    title: BX.message('TASKS_LIST_POPUP_RESPONSIBLE'),
                    limit: 1, //TODO
                    items: this.getList(),
                    sections: [{id: 'default'}]
                },
                userList => {
                    this.list = userList;

                    this.list.setListener(
                        (event, data) => {
                            const eventHandlers = {
                                onRecipientsReceived: this.onRecipientsReceived,
                                onUserTypeText: this.onUserTypeText
                            };
                            Log.debug('Fire event: ' + event);
                            if (eventHandlers[event]) {
                                eventHandlers[event].apply(this, [data]);
                            }
                        }
                    );
                }
            );
        }

        getList() {
            let ar = [];

            this.usersSort.forEach(row => {
                ar.push(this.users.get(row));
            });

            return ar;
        }

        onRecipientsReceived(items) {
            if (this.handlers.onSelect && items[0].id) {
                const item = items[0];
                const itemId = item.id.toString();

                if (!this.users.has(itemId)) {
                    this.users.set(itemId, {
                        id: item.id,
                        title: item.title,
                        imageUrl: item.imageUrl,
                        color: "#f0f0f0",
                        useLetterImage: true,
                        sectionCode: 'default'
                    });
                }

                delete this.usersSort[this.usersSort.indexOf(itemId)];
                this.usersSort.unshift(itemId);

                this.saveCache();

                this.handlers.onSelect(items);
            }
        }

        loadUsersFromCache() {
            let cacheData = this.cache.get();

            if (typeof cacheData !== 'object') {
                return false;
            }

            try {
                cacheData.list.forEach(item => {
                    if (item.id && this.users.size < 50) {
                        this.users.set(item.id.toString(), item);
                    }
                });
                this.usersSort = cacheData.sort;

                Log.debug('Loaded from cache');
                return true;
            } catch (e) {
                return false;
            }
        }

        loadUsersFromComponent() {
            for (let key in result.userList) {
                if (!result.userList.hasOwnProperty(key)) {
                    continue;
                }

                let item = result.userList[key];

                if (this.users.size < 50) {
                    this.usersSort.push(String(item.id));
                    this.users.set(String(item.id), {
                        id: item.id,
                        title: item.name,
                        imageUrl: item.icon,
                        color: "#f0f0f0",
                        useLetterImage: true,
                        sectionCode: 'default'
                    });
                }
            }

            Log.debug('Loaded from component');
            this.saveCache();
        }

        saveCache() {
            this.cache.set({list: [...this.users.values()], sort: this.usersSort});
        }

        static getFormattedName(userData = {}) {
            let name = "#NAME# #SECONDNAME#";
            name = name.replace("#NAME#", userData.NAME ? userData.NAME : "");
            name = name.replace("#SECONDNAME#", userData.LAST_NAME ? userData.LAST_NAME : "");
            if (name.trim() == "") {
                name = userData.EMAIL;
            }

            return name;
        }
    }

    class Order {
        constructor(userId, ownerId) {
            this.cache = new Cache('order');
            const defaultOrderValues = this.cache.get();

            this.userId = Number(userId);
            this.ownerId = Number(ownerId);

            this._direction = defaultOrderValues && defaultOrderValues.direction ? defaultOrderValues.direction : 'asc';
            this._field = defaultOrderValues && defaultOrderValues.field ? defaultOrderValues.field : 'created_date';
        }

        get fields() {
            let fields = [
                {
                    name: BX.message('TASKS_POPUP_MENU_ORDER_FIELD_CREATED_DATE'),
                    value: 'created_date'
                },
                {
                    name: BX.message('TASKS_POPUP_MENU_ORDER_FIELD_DEADLINE'),
                    value: 'deadline'
                }
            ];

            if (this.ownerId === this.userId) // watch your tasks
            {
                fields.push({
                    name: BX.message('TASKS_POPUP_MENU_ORDER_FIELD_SORTING'),
                    value: 'sorting'
                });
            }

            return fields;
        }

        get direction() {
            return this._direction;
        }

        set direction(value) {
            this._direction = value;
            this.save();
        }

        get field() {
            return this._field;
        }

        set field(value) {
            this._field = value;
            this.save();
        }

        save() {
            this.cache.set({
                direction: this.direction,
                field: this.field
            });
        }
    }

    class SectionsInternal {
        constructor() {
            console.log('Load SectionsInternal');
            this.cache = new Cache('tasks.task.list.sections');
            this.cacheData = this.cache.get();
            if (typeof this.cacheData != "object") {
                this.cache = {folded:{}};
            }
            if (typeof this.cacheData.folded != "object") {
                this.cacheData.folded = {};
            }

            this.clear();
            console.log('Loaded');
            console.log(this.cacheData);
        };

        static getInstance() {
            return (SectionsInternal.instance == null)
                ? SectionsInternal.instance = new SectionsInternal()
                : SectionsInternal.instance;
        }

        saveCache()
        {
            console.log('save cache');
            console.log({cache: this.cacheData});
            this.cache.set(this.cacheData);
        }

        isFolded(sectionId)
        {
            return this.cacheData.folded[sectionId];
        }

        static onFolded(section)
        {
            console.log('onFolded');

            const self = SectionsInternal.getInstance();


            console.log(section);
            if(section.folded)
            {
                console.log('set');
                self.cacheData.folded[section.id] = section.folded;
            }
            else
            {
                console.log('delete');
                delete self.cacheData.folded[section.id];
            }
            self.saveCache();
        }

        add(item) {

            if(!this.items[item.id])
            {
                this.items[item.id] = {};
            }

            this.items[item.id].backgroundColor = '#FFFFFF';
            this.items[item.id].styles = {title: {font: {size: 18}}};


            this.items[item.id].title = item.title || '';
            this.items[item.id].id = item.id || 'default';
            this.items[item.id].foldable = item.foldable || false;
            this.items[item.id].folded =
               this.isFolded(item.id);
            // item.folded || false;

            item.badgeValue = item.badgeValue || 0;

            if(!this.items[item.id].badgeValue)
            {
                this.items[item.id].badgeValue = 0;
            }

            this.items[item.id].badgeValue += item.badgeValue;
        }

        has(id)
        {
            return 'section-' + id in this.items;
        }

        remove(id)
        {
            delete this.items['section-' + id];
        }

        get list()
        {
            return Object.values(this.items);

            // let list = [];
            //
            // for(let i in this.items)
            // {
            //     list.push(this.items[i]);
            // }
            //
            // return list;
        }

        get keys()
        {
            return Object.keys(this.list);
        }

        clear()
        {
            this.items = {
                'default': {title: '', id: 'default', foldable: false, folded: false, badgeValue: 0},
                'section-0': {title: '', id: 'section-0', foldable: false, folded: false, badgeValue: 0}
            };
        }
    }

    return (new App(list, userId));
})();