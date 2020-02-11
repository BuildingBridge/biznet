"use strict";
/**
 * @bxjs_lang_path component.php
 */

include("InAppNotifier");

(function () {
    class App {
        constructor(webtabs, userId) {
            this.webtabs = webtabs;
            this.userId = userId;
            this.currentUser = result.settings.userInfo;
            this.checklistData = {
                opened: 0,
                closed: 0,
                all: 0
            };

            this.currentOpenTab = 'taskTab';

            this.task = new Task(this.currentUser);
            this.taskId = BX.componentParameters.get('TASK_ID');

            this.rest = new Request();
            this.rest.call('get', {taskId: this.taskId, select: App._selectFields}).then(
                response => {
                    const row = response.result.task;
                    this.task.setData(row);

                    this.updateTabs({taskMessageCount: this.task.getMessageCount()});


                    if(row.checklist && Object.keys(row.checklist).length)
                    {
                        Object.entries(row.checklist).forEach(row=>{
                            let item = row[1];

                            if(item.title != '===') {
                                this.checklistData.all++;
                                if (item.isComplete == 'Y')
                                    this.checklistData.closed++;
                                else
                                    this.checklistData.opened++;
                            }
                        });
                    }

                    this.updateTabs({checklist: this.checklistData});

                    if(this.task.commentsCount)
                    {
                        this.webtabs.selectTab('commentsTab');
                    }

                    BX.onViewLoaded(() => {
                        this.popupMenu = dialogs.createPopupMenu();
                        this.webtabs.setRightButtons([
                            {
                                type: "more",
                                callback: () => {
                                    this.popupMenu.show();
                                }
                            }
                        ]);
                        this.redrawMenu();
                    });
                },
                response => {
                    console.log(response);
                }
            );

            this.setListeners();

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
                'NEW_COMMENTS_COUNT',
                'FAVORITE',
                'NOT_VIEWED',
                'CHECKLIST',
                'GROUP_ID'
            ];
        }

        redrawMenu() {
            this.popupMenu.setData(this.popupMenuItems, [
                {id: "0"}
            ], (eventName, item) => {
                let url = '';

                if (eventName == 'onItemSelected') {
                    switch (item.id) {
                        default:
                            console.log('Unknown action ' + item.id);
                            break;
                        case 'addSubTask':
                            url = result.settings.taskPaths.addSub.replace(/#taskId#/gi, 0).replace(/#parentTaskId#/gi, this.task._id).replace(/#userId#/gi, this.currentUser.id).replace(/#salt#/gi, new Date().getTime());

                            PageManager.openPage({
                                url: url,
                                cache: false,
                                modal: true
                            });
                            break;
                        case 'addTask':
                            url = result.settings.taskPaths.add.replace(/#taskId#/gi, 0).replace(/#userId#/gi, this.currentUser.id).replace(/#salt#/gi, new Date().getTime());

                            PageManager.openPage({
                                url: url,
                                cache: false,
                                modal: true
                            });
                            break;
                        case 'update':
                            url = result.settings.taskPaths.update.replace(/#taskId#/gi, this.task._id).replace(/#userId#/gi, this.currentUser.id).replace(/#salt#/gi, new Date().getTime());

                            PageManager.openPage({
                                url: url,
                                cache: false,
                                modal: true
                            });
                            break;
                        case 'remove':
                            dialogs.showActionSheet({
                                title: BX.message('TASKS_TASK_DETAIL_CONFIRM_REMOVE'),
                                "callback": item => {
                                    if (item.id == 'yes') {
                                        include("InAppNotifier");

                                        InAppNotifier.showNotification({
                                            title:BX.message('TASKS_TASK_DETAIL_TASK_WAS_REMOVED'),
                                            backgroundColor:"#333333"
                                        });

                                        this.webtabs.close();
                                        this.rest.call('delete', {taskId: this.task._id})
                                            .then(
                                                response => {

                                                },
                                                response => {

                                                }
                                            );
                                    }
                                },
                                "items": [
                                    {
                                        id: 'yes',
                                        title: BX.message('TASKS_TASK_DETAIL_CONFIRM_REMOVE_YES'),
                                        code: "answer"
                                    },
                                    {
                                        id: 'no',
                                        title: BX.message('TASKS_TASK_DETAIL_CONFIRM_REMOVE_NO'),
                                        code: "answer"
                                    },
                                ]
                            });
                            break;
                        case 'complete':
                            console.log('complete');
                            this.task.status = Task.statusList.completed;
                            BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                            this.updateTask();
                            this.redrawMenu();

                            this.task.complete().then(
                                () => {
                                    BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                                    this.updateTask();
                                    this.redrawMenu();

                                }
                            );
                            break;

                        case 'start':
                            this.task.status = Task.statusList.inprogress;
                            BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                            this.updateTask();
                            this.redrawMenu();

                            this.task.start().then(
                                () => {
                                    BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                                    this.updateTask();
                                    this.redrawMenu();
                                }
                            );
                            break;
                        case 'renew':
                            this.task.status = Task.statusList.pending;
                            BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                            this.updateTask();
                            this.redrawMenu();

                            this.task.renew().then(
                                () => {
                                    BX.postWebEvent('tasks.task.update', {status: this.task.status}, true);
                                    this.updateTask();
                                    this.redrawMenu();
                                }
                            );
                            break;
                        case 'pause':
                            this.task.status = Task.statusList.pending;
                            BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                            this.updateTask();
                            this.redrawMenu();

                            this.task.pause().then(
                                () => {
                                    BX.postWebEvent('tasks.task.update', {status: this.task.status}, true);
                                    this.updateTask();
                                    this.redrawMenu();
                                }
                            );
                            break;
                        case 'disapprove':
                            console.log('disapprove');
                            this.task.status = Task.statusList.pending;
                            BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                            this.updateTask();
                            this.redrawMenu();

                            this.task.disapprove().then(
                                () => {
                                    BX.postWebEvent('tasks.task.update', {status: this.task.status}, true);
                                    this.updateTask();
                                    this.redrawMenu();
                                }
                            );
                            break;
                        case 'approve':
                            this.task.status = Task.statusList.completed;
                            BX.postWebEvent('tasks.task.update', {status:this.task.status}, true);
                            this.updateTask();
                            this.redrawMenu();

                            this.task.approve().then(
                                () => {
                                    BX.postWebEvent('tasks.task.update', {status: this.task.status}, true);
                                    this.updateTask();
                                    this.redrawMenu();
                                }
                            );
                            break;
                        case 'delegate':
                            break;
                        case 'favorite.add':
                            BX.postWebEvent('tasks.task.update', {favorite:true}, true);
                            this.task.rawAccess['favorite.add'] = false;
                            this.task.rawAccess['favorite.delete'] = true;
                            this.updateTask();
                            this.redrawMenu();

                            this.task.favorite().add().then(
                                () => {
                                    // this.webtabs.selectTab('taskTab');
                                },
                                ()=>{
                                    this.task.rawAccess['favorite.add'] = true;
                                    this.task.rawAccess['favorite.delete'] = false;
                                    BX.postWebEvent('tasks.task.update', {favorite:false}, true);
                                    this.updateTask();
                                    this.redrawMenu();

                                }
                            );
                            break;
                        case 'favorite.remove':
                            BX.postWebEvent('tasks.task.update', {favorite:false}, true);

                            this.task.rawAccess['favorite.add'] = true;
                            this.task.rawAccess['favorite.delete'] = false;
                            this.updateTask();
                            this.redrawMenu();

                            this.task.favorite().remove().then(
                                () => {
                                    // this.webtabs.refreshPage();
                                    // this.webtabs.selectTab('taskTab');
                                },
                                ()=>{
                                    this.task.rawAccess['favorite.add'] = false;
                                    this.task.rawAccess['favorite.delete'] = true;
                                    BX.postWebEvent('tasks.task.update', {favorite:true}, true);
                                    this.updateTask();
                                    this.redrawMenu();
                                }
                            );
                            break;
                    }
                }
            });
        }

        updateTabs(data) {
            console.log('updateTabs', data);
            if (data.checklist) {
                console.log('set checklist: '+ data.checklist.closed + '/' + data.checklist.all);

                if(data.checklist.closed + data.checklist.all > 0) {
                    this.webtabs.updateBadge('checklistTab', data.checklist.closed + '/' + data.checklist.all);
                }
                else
                {
                    this.webtabs.updateBadge('checklistTab', '');
                }
            }
            if (data.taskMessageCount) {
                console.log('set taskMessageCount: '+data.taskMessageCount);
                this.webtabs.updateBadge('taskTab', String(data.taskMessageCount));
            }
            if (data.hasOwnProperty('newCommentsCount')) {
                console.log('set newCommentsCount: '+data.newCommentsCount);
                this.webtabs.updateBadge('commentTab', String(data.newCommentsCount));

                BX.postComponentEvent('task.view.onCommentsRead', [{taskId: this.task.id}], "tasks.list");
            }
        }

        onTabSelected(data)
        {
            console.log('onTabSelected', data);

            if(data.tab.id==="commentTab")
            {
                this.updateTabs({newCommentsCount: ''});
            }

            this.currentOpenTab = data.tab.id;
        }

        setListeners() {
            BX.addCustomEvent("onTabSelect", (eventData) => {
                console.log('onTabSelect');
                this.webtabs.selectTab(eventData.tab);
                this.currentOpenTab = eventData.tab;
            });

            const eventHandlers = {
                onTabSelected: {
                    cb: (data) => {
                        this.onTabSelected(data);
                    },
                    ctx: this.webtabs
                },
            };

            this.webtabs.setListener((event, data) => {
                console.log('Fire event: taskView.' + event);
                if (eventHandlers[event]) {
                    eventHandlers[event].cb.apply(
                        eventHandlers[event].ctx,
                        [data]
                    );
                }
            });

            BX.addCustomEvent("onMobileTaskViewCheckListToggle", (eventData) => {
                console.log('onMobileTaskViewCheckListToggle', eventData);

                if(eventData.checklistToggle)
                {
                    if(eventData.checklistToggle.checked === true)
                    {
                        this.checklistData.closed ++;
                        this.checklistData.opened --;
                    }
                    if(eventData.checklistToggle.checked === false)
                    {
                        this.checklistData.closed --;
                        this.checklistData.opened ++;
                    }
                    this.updateTabs({checklist: this.checklistData});
                }
            });
            BX.addCustomEvent("onMobileTaskViewCheckListAdd", (eventData) => {
                console.log('onMobileTaskViewCheckListAdd', eventData);

                if(eventData.checklistItem && eventData.checklistItem.TITLE !== '===')
                {

                    this.checklistData.all ++;

                    this.updateTabs({checklist: this.checklistData});
                }
            });
            BX.addCustomEvent("onMobileTaskViewCheckListRemove", (eventData) => {
                console.log('onMobileTaskViewCheckListRemove', eventData);
                if(eventData.id && eventData.isSeparator !== 'Y')
                {
                    if(eventData.checked)
                    {
                        this.checklistData.closed --;
                    }
                    else
                    {
                        this.checklistData.opened --;
                    }

                    this.checklistData.all --;

                    this.updateTabs({checklist: this.checklistData});
                }
            });


            const handlers = {
                task_update: this.onPullUpdate,
                task_remove: this.onPullDelete,
                comment_add: this.onPullComment
            };

            BX.addCustomEvent("onPullEvent-tasks",
            (command, params, extra) => {
                if(handlers[command])
                {
                    handlers[command].apply(this, [params]);
                }
            });

        }

        onPullUpdate()
        {
            // this.webtabs.refreshPage();
            // this.webtabs.selectTab('taskTab');
        }

        onPullDelete()
        {

        }

        onPullComment(data)
        {
            console.log('onPullComment');
            const xmlId = data.ENTITY_XML_ID.split('_');
            if(xmlId[0] !== 'TASK')
            {
                console.log('Not task comment');
                return false;
            }

            const taskId = xmlId[1];

            if(Number(data.OWNER_ID) === Number(this.currentUser.id) || taskId != this.task.id)
            {
                console.log('taskId != this.task.id');
                return false;
            }

            if(this.currentOpenTab === 'commentTab')
            {
                this.task.newCommentsCount=0;
                this.updateTabs({newCommentsCount: ''});
            }
            else {

                this.task.newCommentsCount = this.task.newCommentsCount + 1;
                this.updateTabs({newCommentsCount: this.task.newCommentsCount});
            }
        }

        updateTask(taskData) {

        }

        get popupMenuItems() {
            let items = [];

            items.push({id: 'addTask', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_TASK_ADD")});
            items.push({id: 'addSubTask', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_TASK_ADD_SUBTASK")});

            if (this.can('complete')) {
                items.push({id: 'complete', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_CLOSE_TASK")});
            }

            if (this.can('start')) {
                items.push({id: 'start', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_START_TASK")});
            }

            if (this.can('renew')) {
                items.push({id: 'renew', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_RENEW_TASK")});
            }

            if (this.can('pause')) {
                items.push({id: 'pause', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_PAUSE_TASK")});
            }

            if (this.can('disapprove')) {
                items.push({id: 'disapprove', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_REDO_TASK")});
            }

            if (this.can('approve')) {
                items.push({id: 'approve', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_APPROVE_TASK")});
            }

            // if(this.can('delegate'))
            // {
            // 	items.push({id: 'delegate', sectionCode: "0" , title: BX.message("TASKS_TASK_DETAIL_BTN_DELEGATE_TASK")});
            // }

            if (this.can('remove')) {
                items.push({id: 'remove', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_REMOVE")});
            }

            if (this.can('update')) {
                items.push({id: 'update', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_EDIT")});
            }

            // if (this.can('defer')) {
            //     items.push({id: 'defer', sectionCode: "0", title: BX.message("TASKS_TASK_DETAIL_BTN_DEFER_TASK")});
            // }

            if (this.can('favorite.add')) {
                items.push({
                    id: 'favorite.add',
                    sectionCode: "0",
                    title: BX.message("TASKS_TASK_DETAIL_BTN_ADD_FAVORITE_TASK")
                });
            }

            if (this.can('favorite.remove')) {
                items.push({
                    id: 'favorite.remove',
                    sectionCode: "0",
                    title: BX.message("TASKS_TASK_DETAIL_BTN_DELETE_FAVORITE_TASK")
                });
            }

            return items;
        }

        can(right) {
            return this.task.can.hasOwnProperty(right) && Boolean(this.task.can[right]);
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
                BX.rest.callMethod(this.restNamespace + method, params || {}, response => {
                    this.currentAnswer = response;

                    if (response.error()) {
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

    return (new App(webtabs, userId));
})();