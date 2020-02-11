"use strict";

(function(){

    class TaskBackgroundAction {
        constructor() {
            BX.addCustomEvent(
                "taskbackground::task::action",
                (data, taskId, params = {}, extra = false, delay = null) =>
                    TaskBackgroundAction.executeAction(data, taskId, params, extra = false, delay = null)
            );
        }

        static executeAction(data, taskId, params, extra = false, delay = null)
        {
            console.info('TaskBackgroundAction.executeAction', data);

            if(data.taskId) {
                if (0 && Application.getApiVersion() >= 30) {
                    TaskBackgroundAction.openTaskComponentByTaskId(data.taskId, data);
                } else {
                    TaskBackgroundAction.loadTaskPageByTaskId(data.taskId, data);
                }
            }

            if(data.groupId) {
                if (Application.getApiVersion() >= 30) {
                    TaskBackgroundAction.openTaskListComponentByGroupId(data.groupId, {});
                }
            }

        }

        static loadTaskPageByTaskId(taskId, data)
        {
            PageManager.openPage({url:TaskBackgroundAction.makeComponentTaskUrl(taskId)});
        }

        static openTaskListComponentByGroupId(groupId, data)
        {
            console.log('openTaskListComponentByGroupId');
            data = data || {};

            data.params = {
                    "COMPONENT_CODE" :"tasks.list",
                    "GROUP_ID" :groupId,
                    "SITE_ID" :env.siteId,
                    "LANGUAGE_ID" :env.languageId,
                    "SITE_DIR" :env.siteDir,
                    "PATH_TO_TASK_ADD":env.siteDir+"mobile/tasks/snmrouter/?routePage=#action#&TASK_ID=#taskId#"
                };
            data.path = availableComponents["tasks.list"]["publicUrl"];
            data.canOpenInDefault= true;
            TaskView.open(data);
        }

        static openTaskComponentByTaskId(taskId, data) {
            data = data || {};
            data.publicUrl = data.publicUrl || availableComponents['tasks.view'].publicUrl;
            data.selectedTab = data.selectedTab || 'taskTab';
			data.messageId = data.messageId || 0;

            const param = {
                name: "JSStackComponent",
                componentCode: "tasks.view",
                canOpenInDefault: true,
                scriptPath: data.publicUrl,
                rootWidget: {
                    name: "webtabs",
                    settings: {
                        objectName: "webtabs",
                        swipeToClose: true,
                        title: data.title ? data.title : null,
                        modal: true,
                        tabs: [
                            {
                                sort: 0,
                                id: "taskTab",
                                title: BX.message('MOBILE_TASKS_VIEW_TAB_TASK'),
                                imageName: "task",
                                badgeValue: ''//String(this.getMessageCount() > 0 ? this.getMessageCount() : '')
                            },
                            {
                                sort: 1,
                                id: "checklistTab",
                                title: BX.message('MOBILE_TASKS_VIEW_TAB_CHECKLIST'),
                                imageName: "checklist",
                                badgeValue: ""
                            },
                            {
                                sort: 2,
                                id: "filesTab",
                                title: BX.message('MOBILE_TASKS_VIEW_TAB_FILES'),
                                imageName: "file"
                            },
                            {
                                sort: 3,
                                id: "commentTab",
                                title: BX.message('MOBILE_TASKS_VIEW_TAB_COMMENT'),
                                imageName: "chat",
                                badgeValue: ''//this.newCommentsCount > 0 ? String(this.newCommentsCount) : ''
                            }
                        ],
                        selectedTab: data.selectedTab,
                        pageUrl: TaskBackgroundAction.makeComponentTaskUrl(taskId, undefined, data.selectedTab, data.messageId)
                    }
                },
                params: {
                    COMPONENT_CODE: "tasks.view",
                    USER_ID: 0,//this.currentUser.id,
                    TASK: {},//this
                    TASK_ID: taskId
                }

            };

            PageManager.openComponent("JSStackComponent", param);
            console.log({title: 'PageManager.openComponent', param: param});
        }

        static makeComponentTaskUrl(taskId, action = 'view', tabId = "taskTab", messageId = 0)
        {
			return ('/mobile/tasks/snmrouter/?routePage=#action#&TASK_ID=#taskId#&MID=#messageId#&tabId='+tabId)
                .replace('#action#', action)
                .replace('#taskId#', taskId)
				.replace('#messageId#', messageId);
        }
    }

    this.TaskBackgroundAction = new TaskBackgroundAction();
})();