{"version":3,"sources":["script.js"],"names":["BX","CrmQuoteStorageType","undefined","file","webdav","diskfiles","CrmQuoteMode","edit","view","FilesFieldContainer","settings","this","random","Math","toString","substring","controlMode","container","storageTypeId","parseInt","getSetting","isNaN","getDefaultStorageTypeId","uploaderName","setSetting","prepareWebDavUploader","prepareDiskUploader","prepareFileUploader","prototype","name","defaultval","val","getMessage","getDialogElements","form","document","forms","elements","mode","vals","type","isNotEmptyString","uploader","CrmWebDavUploader","items","cleanLayout","create","urlSelect","urlUpload","urlShow","elementInfoLoader","delegate","getWebDavElementInfo","msg","loading","file_exists","access_denied","title","attachFile","dragFile","selectFile","selectFromLib","loadFiles","setMode","setValues","layout","CrmDiskUploader","diskAttachFiles","diskAttachedFiles","diskSelectFile","diskSelectFileLegend","diskUploadFile","diskUploadFileLegend","controlId","CFileInput","Items","setFiles","style","display","elementId","callback","ajax","url","method","dataType","data","ACTION","ELEMENT_ID","onsuccess","innerData","isFunction","e","onfailure","getWebDavUploaderValues","result","getValues","i","length","push","getDiskUploaderValues","getFileIds","getFileUploaderValues","files","isElementNode","value","isArray","onSaveHandler","inputContainer","CID","arr","findChildren","tag","attr","Array","match","remove","appendChild","attrs","CrmQuoteEditor","_id","_settings","_formId","_formManager","_productRowEditorId","_productEditor","_currencyElement","_opportunityElement","_filesFieldContainer","initialize","id","util","getRandomString","findChild","CrmProductEditor","getDefault","disabled","getProductCount","addCustomEvent","_handleProductAddRemove","_handleSumTotalChange","bind","_handleCurrencyChange","el","hasClass","removeClass","addClass","setAttribute","btns","bid","_onSaveHandler","CrmEditFormManager","toLowerCase","_onProductEditorFocusChange","hasOwnProperty","product","prodEditor","_editor","ttl","currencyId","prevCurrencyId","getCurrencyId","setCurrencyId","oportunity","parseFloat","convertMoney","_callbackAfterProductEditorConvertMoney","sum","getId","sender","focused","self"],"mappings":"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","file":"script.map.js"}