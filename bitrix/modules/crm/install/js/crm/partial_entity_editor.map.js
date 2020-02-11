{"version":3,"sources":["partial_entity_editor.js"],"names":["BX","namespace","Crm","PartialEditorDialog","this","_id","_settings","_serviceUrl","_entityTypeId","_entityTypeName","_entityId","_fieldNames","_html","_editor","_wrapper","_popup","_buttons","_isLocked","_entityUpdateSuccessHandler","delegate","onEntityUpdateSuccess","_entityUpdateFailureHandler","onEntityUpdateFailure","_entityValidationFailureHandler","onEntityValidationFailure","prototype","initialize","id","settings","type","isNotEmptyString","util","getRandomString","prop","getInteger","CrmEntityType","enumeration","undefined","resolveName","getString","resolveId","getArray","_isAccepted","getSetting","name","defaultValue","get","getId","getEditorId","toLowerCase","isLoaded","getServiceUrl","entityEditorUrls","load","ajax","post","ACTION","ACTION_ENTITY_TYPE_NAME","ACTION_ENTITY_ID","GUID","FIELDS","PARAMS","CONTEXT","getObject","TITLE","FORCE_DEFAULT_CONFIG","ENABLE_CONFIG_SCOPE_TOGGLE","ENABLE_CONFIGURATION_UPDATE","ENABLE_FIELDS_CONTEXT_MENU","IS_EMBEDDED","result","editor","EntityEditor","release","innerOpen","bind","open","PopupWindow","autoHide","draggable","closeByEsc","offsetLeft","offsetTop","zIndex","bindOptions","forceBindPosition","content","prepareContent","events","onPopupShow","onPopupClose","onPopupDestroy","show","close","isOpen","isShown","create","props","style","width","innerHTML","buttonWrapper","className","appendChild","DialogButtonType","names","accept","text","message","click","onSaveButtonClick","cancel","onCancelButtonClick","e","addClass","addCustomEvent","window","save","eventParams","removeClass","removeCustomEvent","onCustomEvent","entityTypeId","entityTypeName","entityId","entityData","bid","isCancelled","sender","eventArgs","setTimeout","adjustPosition","destroy","items","registerEntityEditorUrl","url","hasOpenItems","key","hasOwnProperty","getItem","self"],"mappings":"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","file":"partial_entity_editor.map.js"}