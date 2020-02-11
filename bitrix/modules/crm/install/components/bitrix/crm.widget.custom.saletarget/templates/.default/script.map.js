{"version":3,"sources":["script.js"],"names":["BX","namespace","Crm","Widget","Custom","SaleTarget","Template","name","this","loopData","rootNode","type","isDomNode","clone","getTemplateNode","prototype","tpl","window","document","querySelector","node","create","html","innerHTML","replaceIncludes","getTemplateAttribute","attribute","getAttribute","targetNode","includes","querySelectorAll","i","length","templateName","appendChild","loop","loopName","data","loopFn","include","copyNode","firstElementChild","itemNode","cleanNode","itemTpl","get","loopAppend","item","applyIf","conditionName","conditionValue","toString","nodes","value","split","remove","getAll","Array","slice","call","Label","getMonths","1","message","2","3","4","5","6","7","8","9","10","11","12","getDaysOfMonth","getQuarters","getHalfs","getDealPluralRules","dealPlural","quantity","parseInt","labelIndex","month","date","getMonth","months","day","getDate","days","replace","quarter","q","halfYear","h","number","opt","dec","tho","util","number_format","target","Target","Type","Quantity","result","valueAppended","pieces","copyPeriod","periodType","Period","Year","Half","Quarter","Month","config","year","half","calculate","getTypeList","id","getYearList","start","Date","getFullYear","list","push","getHalfList","shortName","getQuarterList","getMonthList","getId","calculateMonth","calculateQuarter","calculateHalf","calculateYear","startDate","endDate","firstMonth","getLabel","dateLabel","getDayPositionPercent","setHours","check","getTime","left","right","next","getLeftBorder","getRightBorder","period","goal","current","Sum","remaining","completedPercent","Math","floor","progressPercent","min","append","useAutoHeight","prepareButtons","widget","buttons","CrmCustomWidget","superclass","apply","_data","attrs","className","text","events","click","openConfigDialog","bind","createContentNode","configuration","isNew","changeConfiguration","currencyFormat","getPanel","getCurrencyFormat","totalTarget","max","totalGoal","totalCurrent","textContent","abs","dealCategories","parseJSON","createCompanyContentNode","createCategoryContentNode","createUserContentNode","forEach","todayPosition","style","addClass","width","previousConfigurationId","nextConfigurationId","configId","me","_contentWrapper","opacity","ajaxData","sessid","bitrix_sessid","site","action","configuration_id","ajax","url","ajaxUrl","method","dataType","onsuccess","response","onfailure","template","category","row","toggleClass","isAlone","users","user","title","photo","background","active","isArray","ConfigPopup","categories","show","top"],"mappings":"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","file":"script.map.js"}