ALTER TABLE B_CRM_LEAD DROP CONSTRAINT DF_B_CRM_LEAD_DATE_MODIFY
GO
ALTER TABLE B_CRM_LEAD DROP CONSTRAINT DF_B_CRM_LEAD_OPENED
GO
ALTER TABLE B_CRM_LEAD DROP CONSTRAINT DF_B_CRM_LEAD_DATE_CREATE
GO
ALTER TABLE B_CRM_LEAD DROP CONSTRAINT PK_B_CRM_LEAD
GO
ALTER TABLE B_CRM_DEAL DROP CONSTRAINT DF_B_CRM_DEAL_DATE_MODIFY
GO
ALTER TABLE B_CRM_DEAL DROP CONSTRAINT DF_B_CRM_DEAL_OPENED
GO
ALTER TABLE B_CRM_DEAL DROP CONSTRAINT DF_B_CRM_DEAL_DATE_CREATE
GO
ALTER TABLE B_CRM_DEAL DROP CONSTRAINT DF_B_CRM_DEAL_CLOSED
GO
ALTER TABLE B_CRM_DEAL DROP CONSTRAINT PK_B_CRM_DEAL
GO
ALTER TABLE B_CRM_CONTACT DROP CONSTRAINT DF_B_CRM_CONTACT_DATE_MODIFY
GO
ALTER TABLE B_CRM_CONTACT DROP CONSTRAINT DF_B_CRM_CONTACT_OPENED
GO
ALTER TABLE B_CRM_CONTACT DROP CONSTRAINT DF_B_CRM_CONTACT_DATE_CREATE
GO
ALTER TABLE B_CRM_CONTACT DROP CONSTRAINT DF_B_CRM_CONTACT_EXPORT
GO
ALTER TABLE B_CRM_CONTACT DROP CONSTRAINT PK_B_CRM_CONTACT
GO
ALTER TABLE B_CRM_COMPANY DROP CONSTRAINT DF_B_CRM_COMPANY_DATE_MODIFY
GO
ALTER TABLE B_CRM_COMPANY DROP CONSTRAINT DF_B_CRM_COMPANY_OPENED
GO
ALTER TABLE B_CRM_COMPANY DROP CONSTRAINT DF_B_CRM_COMPANY_DATE_CREATE
GO
ALTER TABLE B_CRM_COMPANY DROP CONSTRAINT PK_B_CRM_COMPANY
GO
ALTER TABLE B_CRM_STATUS DROP CONSTRAINT PK_B_CRM_STATUS
GO
ALTER TABLE B_CRM_FIELD_MULTI DROP CONSTRAINT PK_B_CRM_FIELD_MULTI
GO
ALTER TABLE B_CRM_EVENT DROP CONSTRAINT PK_B_CRM_EVENT
GO
ALTER TABLE B_CRM_EVENT_RELATIONS DROP CONSTRAINT PK_B_CRM_EVENT_RELATIONS
GO
ALTER TABLE B_CRM_ENTITY_LOCK DROP CONSTRAINT PK_B_CRM_ENTITY_LOCK
GO
DROP TABLE B_CRM_LEAD
GO
DROP TABLE B_CRM_DEAL
GO
DROP TABLE B_CRM_CONTACT
GO
DROP TABLE B_CRM_COMPANY
GO
DROP TABLE B_CRM_STATUS
GO
DROP TABLE B_CRM_FIELD_MULTI
GO
DROP TABLE B_CRM_EVENT
GO
DROP TABLE B_CRM_EVENT_RELATIONS
GO
DROP TABLE B_CRM_ENTITY_LOCK
GO
ALTER TABLE B_CRM_ENTITY_PERMS DROP CONSTRAINT PK_B_CRM_ENTITY_PERMS
GO
DROP TABLE B_CRM_ENTITY_PERMS
GO
ALTER TABLE B_CRM_ROLE DROP CONSTRAINT PK_B_CRM_ROLE
GO
DROP TABLE B_CRM_ROLE
GO
ALTER TABLE B_CRM_ROLE_PERMS DROP CONSTRAINT PK_B_CRM_ROLE_PERMS
GO
ALTER TABLE B_CRM_ROLE_PERMS DROP CONSTRAINT DF_B_CRM_ROLE_PERMS_FIELD
GO
ALTER TABLE B_CRM_ROLE_PERMS DROP CONSTRAINT DF_B_CRM_ROLE_PERMS_FIELD_VALUE
GO
ALTER TABLE B_CRM_ROLE_PERMS DROP CONSTRAINT DF_B_CRM_ROLE_PERMS_ATTR
GO
DROP TABLE B_CRM_ROLE_PERMS
GO
ALTER TABLE B_CRM_ROLE_RELATION DROP CONSTRAINT PK_B_CRM_ROLE_RELATION
GO
DROP TABLE B_CRM_ROLE_RELATION
GO

IF OBJECT_ID(N'B_CRM_EXTERNAL_SALE', N'U') IS NOT NULL
  DROP TABLE B_CRM_EXTERNAL_SALE
GO

IF OBJECT_ID(N'B_CRM_PRODUCT_ROW_CFG', N'U') IS NOT NULL
  DROP TABLE B_CRM_PRODUCT_ROW_CFG
GO

IF OBJECT_ID(N'B_CRM_PRODUCT_ROW', N'U') IS NOT NULL
  DROP TABLE B_CRM_PRODUCT_ROW
GO

IF OBJECT_ID(N'B_CRM_PRODUCT', N'U') IS NOT NULL
  DROP TABLE B_CRM_PRODUCT
GO

IF OBJECT_ID(N'B_CRM_CATALOG', N'U') IS NOT NULL
  DROP TABLE B_CRM_CATALOG
GO

IF OBJECT_ID(N'B_CRM_USR_ACT', N'U') IS NOT NULL
  DROP TABLE B_CRM_USR_ACT
GO

IF OBJECT_ID(N'B_CRM_ACT_ELEM', N'U') IS NOT NULL
  DROP TABLE B_CRM_ACT_ELEM
GO

IF OBJECT_ID(N'B_CRM_ACT_COMM', N'U') IS NOT NULL
  DROP TABLE B_CRM_ACT_COMM
GO

IF OBJECT_ID(N'B_CRM_ACT_BIND', N'U') IS NOT NULL
  DROP TABLE B_CRM_ACT_BIND
GO

IF OBJECT_ID(N'B_CRM_ACT', N'U') IS NOT NULL
  DROP TABLE B_CRM_ACT
GO

IF OBJECT_ID(N'B_CRM_USR_MT', N'U') IS NOT NULL
  DROP TABLE B_CRM_USR_MT
GO

IF OBJECT_ID(N'B_CRM_SL_REL', N'U') IS NOT NULL
  DROP TABLE B_CRM_SL_REL
GO

IF OBJECT_ID(N'B_CRM_SL_SUBSCR', N'U') IS NOT NULL
  DROP TABLE B_CRM_SL_SUBSCR
GO

IF OBJECT_ID(N'B_CRM_QUOTE', N'U') IS NOT NULL
	DROP TABLE B_CRM_QUOTE
GO

IF OBJECT_ID(N'B_CRM_QUOTE_ELEM', N'U') IS NOT NULL
	DROP TABLE B_CRM_QUOTE_ELEM
GO

IF OBJECT_ID(N'B_CRM_BIZ_TYPE', N'U') IS NOT NULL
  DROP TABLE B_CRM_BIZ_TYPE
GO

IF OBJECT_ID(N'B_CRM_DP_ORG_MCD', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_ORG_MCD
GO

IF OBJECT_ID(N'B_CRM_DP_COMM_MCD', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_COMM_MCD
GO

IF OBJECT_ID(N'B_CRM_DP_PRSN_MCD', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_PRSN_MCD
GO

IF OBJECT_ID(N'B_CRM_DP_INDEX', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_INDEX
GO

IF OBJECT_ID(N'B_CRM_DP_INDEX_MISMATCH', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_INDEX_MISMATCH
GO

IF OBJECT_ID(N'B_CRM_DP_ENTITY_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_ENTITY_STAT
GO

IF OBJECT_ID(N'B_CRM_DP_ENTITY_HASH', N'U') IS NOT NULL
  DROP TABLE B_CRM_DP_ENTITY_HASH
GO

IF OBJECT_ID(N'B_CRM_ADDR', N'U') IS NOT NULL
  DROP TABLE B_CRM_ADDR
GO

IF OBJECT_ID(N'B_CRM_DEAL_STAGE_HISTORY', N'U') IS NOT NULL
  DROP TABLE B_CRM_DEAL_STAGE_HISTORY
GO

IF OBJECT_ID(N'B_CRM_DEAL_SUM_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_DEAL_SUM_STAT
GO

IF OBJECT_ID(N'B_CRM_DEAL_INV_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_DEAL_INV_STAT
GO

IF OBJECT_ID(N'B_CRM_DEAL_ACT_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_DEAL_ACT_STAT
GO

IF OBJECT_ID(N'B_CRM_CONV_MAP', N'U') IS NOT NULL
  DROP TABLE B_CRM_CONV_MAP
GO

IF OBJECT_ID(N'B_CRM_LEAD_STATUS_HISTORY', N'U') IS NOT NULL
  DROP TABLE B_CRM_LEAD_STATUS_HISTORY
GO

IF OBJECT_ID(N'B_CRM_LEAD_SUM_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_LEAD_SUM_STAT
GO

IF OBJECT_ID(N'B_CRM_LEAD_ACT_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_LEAD_ACT_STAT
GO

IF OBJECT_ID(N'B_CRM_LEAD_CONV_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_LEAD_CONV_STAT
GO

IF OBJECT_ID(N'B_CRM_INV_STATUS_HISTORY ', N'U') IS NOT NULL
  DROP TABLE B_CRM_INV_STATUS_HISTORY
GO

IF OBJECT_ID(N'B_CRM_INVOICE_SUM_STAT', N'U') IS NOT NULL
  DROP TABLE B_CRM_INVOICE_SUM_STAT
GO

IF OBJECT_ID(N'B_CRM_REQUISITE', N'U') IS NOT NULL
  DROP TABLE B_CRM_REQUISITE
GO

IF OBJECT_ID(N'B_CRM_PRESET', N'U') IS NOT NULL
  DROP TABLE B_CRM_PRESET
GO

IF OBJECT_ID(N'B_CRM_REQUISITE_CFG', N'U') IS NOT NULL
	DROP TABLE B_CRM_REQUISITE_CFG
GO

IF OBJECT_ID(N'B_CRM_REQUISITE_LINK', N'U') IS NOT NULL
	DROP TABLE B_CRM_REQUISITE_LINK
GO

IF OBJECT_ID(N'B_CRM_BANK_DETAIL', N'U') IS NOT NULL
	DROP TABLE B_CRM_BANK_DETAIL
GO

IF OBJECT_ID(N'B_CRM_CONTACT_COMPANY', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_COMPANY
GO

IF OBJECT_ID(N'B_CRM_DEAL_CONTACT', N'U') IS NOT NULL
	DROP TABLE B_CRM_DEAL_CONTACT
GO

IF OBJECT_ID(N'B_CRM_QUOTE_CONTACT', N'U') IS NOT NULL
	DROP TABLE B_CRM_QUOTE_CONTACT
GO

IF OBJECT_ID(N'B_CRM_ENTITY_CFG', N'U') IS NOT NULL
	DROP TABLE B_CRM_ENTITY_CFG
GO

IF OBJECT_ID(N'B_CRM_PS_RQ_CONV_RELATION', N'U') IS NOT NULL
	DROP TABLE B_CRM_PS_RQ_CONV_RELATION
GO

IF OBJECT_ID(N'B_CRM_DEAL_CATEGORY', N'U') IS NOT NULL
	DROP TABLE B_CRM_DEAL_CATEGORY

IF OBJECT_ID(N'B_CRM_WEBFORM', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_COUNTER', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_COUNTER
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_VIEW', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_VIEW
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_START_EDIT', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_START_EDIT
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_FIELD', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_FIELD
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_FIELD_DEP', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_FIELD_DEP
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_FIELD_PRESET', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_FIELD_PRESET
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_RESULT', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_RESULT
GO

IF OBJECT_ID(N'B_CRM_WEBFORM_RESULT_ENTITY', N'U') IS NOT NULL
	DROP TABLE B_CRM_WEBFORM_RESULT_ENTITY
GO

IF OBJECT_ID(N'b_sale_pay_system_ac_bu', N'U') IS NOT NULL
	DROP TABLE b_sale_pay_system_ac_bu
GO

IF OBJECT_ID(N'B_CRM_COMPANY_ACT_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_COMPANY_ACT_STAT
GO

IF OBJECT_ID(N'B_CRM_COMPANY_ACT_STS_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_COMPANY_ACT_STS_STAT
GO

IF OBJECT_ID(N'B_CRM_COMPANY_ACT_MARK_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_COMPANY_ACT_MARK_STAT
GO

IF OBJECT_ID(N'B_CRM_COMPANY_ACT_STM_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_COMPANY_ACT_STM_STAT
GO

IF OBJECT_ID(N'B_CRM_COMPANY_ACT_SUM_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_COMPANY_ACT_SUM_STAT
GO

IF OBJECT_ID(N'B_CRM_COMPANY_GROWTH_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_COMPANY_GROWTH_STAT
GO

IF OBJECT_ID(N'B_CRM_CONTACT_ACT_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_ACT_STAT
GO

IF OBJECT_ID(N'B_CRM_CONTACT_ACT_STS_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_ACT_STS_STAT
GO

IF OBJECT_ID(N'B_CRM_CONTACT_ACT_MARK_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_ACT_MARK_STAT
GO

IF OBJECT_ID(N'B_CRM_CONTACT_ACT_STM_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_ACT_STM_STAT
GO

IF OBJECT_ID(N'B_CRM_CONTACT_ACT_SUM_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_ACT_SUM_STAT
GO

IF OBJECT_ID(N'B_CRM_CONTACT_GROWTH_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_CONTACT_GROWTH_STAT
GO

IF OBJECT_ID(N'B_CRM_ACT_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_ACT_STAT
GO

IF OBJECT_ID(N'B_CRM_BUTTON', N'U') IS NOT NULL
	DROP TABLE B_CRM_BUTTON
GO

IF OBJECT_ID(N'B_CRM_UTM', N'U') IS NOT NULL
	DROP TABLE B_CRM_UTM
GO

IF OBJECT_ID(N'B_CRM_ENTITY_CHANNEL', N'U') IS NOT NULL
	DROP TABLE B_CRM_ENTITY_CHANNEL
GO

IF OBJECT_ID(N'B_CRM_LEAD_CHANNEL_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_LEAD_CHANNEL_STAT
GO

IF OBJECT_ID(N'B_CRM_DEAL_CHANNEL_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_DEAL_CHANNEL_STAT
GO

IF OBJECT_ID(N'B_CRM_ACT_CHANNEL_STAT', N'U') IS NOT NULL
	DROP TABLE B_CRM_ACT_CHANNEL_STAT
GO

IF OBJECT_ID(N'B_CRM_EXT_CHANNEL_CONNECTOR', N'U') IS NOT NULL
	DROP TABLE B_CRM_EXT_CHANNEL_CONNECTOR
GO

IF OBJECT_ID(N'B_CRM_AUTOMATION_TEMPLATE', N'U') IS NOT NULL
	DROP TABLE B_CRM_AUTOMATION_TEMPLATE
GO

IF OBJECT_ID(N'B_CRM_AUTOMATION_TRIGGER', N'U') IS NOT NULL
	DROP TABLE B_CRM_AUTOMATION_TRIGGER
GO

ALTER TABLE B_CRM_KANBAN_SORT DROP CONSTRAINT PK_B_CRM_KANBAN_SORT
GO
DROP TABLE B_CRM_KANBAN_SORT
GO

ALTER TABLE B_CRM_KANBAN_SUPERVISOR DROP CONSTRAINT PK_B_CRM_KANBAN_SUPERVISOR
GO
DROP TABLE B_CRM_KANBAN_SUPERVISOR
GO