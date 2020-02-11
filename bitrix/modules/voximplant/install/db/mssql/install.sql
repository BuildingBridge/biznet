CREATE TABLE B_VOXIMPLANT_PHONE
(
	ID int NOT NULL IDENTITY (1, 1),
	USER_ID int NOT NULL,
	PHONE_NUMBER varchar(20) NOT NULL,
	PHONE_MNEMONIC varchar(20) NULL,
	CONSTRAINT PK_B_VI_P PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_PH_1 ON B_VOXIMPLANT_PHONE (USER_ID, PHONE_NUMBER)
GO
CREATE INDEX IX_VI_PH_2 ON B_VOXIMPLANT_PHONE (USER_ID, PHONE_MNEMONIC)
GO
CREATE INDEX IX_VI_PH_3 ON B_VOXIMPLANT_PHONE (PHONE_NUMBER)
GO


CREATE TABLE B_VOXIMPLANT_STATISTIC
(
	ID int NOT NULL IDENTITY (1, 1),
	ACCOUNT_ID int NULL,
	APPLICATION_ID int NULL,
	APPLICATION_NAME varchar(80) NULL,
	PORTAL_USER_ID int NULL,
	PORTAL_NUMBER varchar(20) NULL,
	PHONE_NUMBER varchar(20) NOT NULL,
	INCOMING varchar(50) not null,
	CALL_ID varchar(255) NOT NULL,
	CALL_CATEGORY varchar(20) NULL,
	CALL_LOG varchar(255) NULL,
	CALL_DIRECTION varchar(255) NULL,
	CALL_DURATION int NOT NULL,
	CALL_START_DATE datetime not null,
	CALL_STATUS int NULL,
	CALL_FAILED_CODE varchar(255) NULL,
	CALL_FAILED_REASON varchar(255) NULL,
	CALL_RECORD_ID int NULL,
	CALL_WEBDAV_ID int NULL,
	CALL_VOTE tinyint NULL,
	COST numeric(11, 4) NULL,
	COST_CURRENCY varchar(50) NULL,
	CRM_ENTITY_TYPE varchar(50) NULL,
	CRM_ENTITY_ID int NULL,
	CRM_ACTIVITY_ID int NULL,
	REST_APP_ID int NULL,
	REST_APP_NAME varchar(255) NULL,
	CONSTRAINT PK_B_VI_S PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_ST_1 ON B_VOXIMPLANT_STATISTIC (PORTAL_USER_ID)
GO
CREATE INDEX IX_VI_ST_2 ON B_VOXIMPLANT_STATISTIC (CALL_START_DATE)
GO
CREATE INDEX IX_VI_ST_3 ON B_VOXIMPLANT_STATISTIC (CALL_FAILED_CODE)
GO
CREATE INDEX IX_VI_ST_4 ON B_VOXIMPLANT_STATISTIC (CALL_CATEGORY)
GO
CREATE INDEX IX_VI_ST_5 ON B_VOXIMPLANT_STATISTIC (CALL_VOTE)
GO
ALTER TABLE B_VOXIMPLANT_STATISTIC ADD CONSTRAINT DF_B_VI_S_I DEFAULT '1' FOR INCOMING
GO
ALTER TABLE B_VOXIMPLANT_STATISTIC ADD CONSTRAINT DF_B_VI_S_CC DEFAULT 'external' FOR CALL_CATEGORY
GO
ALTER TABLE B_VOXIMPLANT_STATISTIC ADD CONSTRAINT DF_B_VI_S_D DEFAULT 0 FOR CALL_DURATION
GO
ALTER TABLE B_VOXIMPLANT_STATISTIC ADD CONSTRAINT DF_B_VI_S_S DEFAULT 0 FOR CALL_STATUS
GO
ALTER TABLE B_VOXIMPLANT_STATISTIC ADD CONSTRAINT DF_B_VI_S_C DEFAULT 0 FOR COST
GO
ALTER TABLE B_VOXIMPLANT_STATISTIC ADD CONSTRAINT DF_B_VI_S_V DEFAULT 0 FOR CALL_VOTE
GO

CREATE TABLE B_VOXIMPLANT_CALL
(
	ID int NOT NULL IDENTITY (1, 1),
	CONFIG_ID int NULL,
	USER_ID int NOT NULL,
	TRANSFER_TYPE varchar(50) NULL,
	TRANSFER_USER_ID int NULL,
   	TRANSFER_PHONE varchar(255) NULL,
	PORTAL_USER_ID int NULL,
	CALL_ID varchar(255) NOT NULL,
	INCOMING varchar(50) null,
	CALLER_ID varchar(255) NULL,
	STATUS varchar(50) NULL,
	CRM char(1) not null,
	CRM_LEAD int NULL,
	CRM_ENTITY_TYPE varchar(50) NULL,
	CRM_ENTITY_ID int NULL,
	CRM_ACTIVITY_ID int NULL,
	ACCESS_URL varchar(255) NULL,
	DATE_CREATE datetime,
	REST_APP_ID int NULL,
	CONSTRAINT PK_B_VI_CL PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_I_1 ON B_VOXIMPLANT_CALL (CALL_ID)
GO
CREATE INDEX IX_VI_I_2 ON B_VOXIMPLANT_CALL (DATE_CREATE)
GO
ALTER TABLE B_VOXIMPLANT_CALL ADD CONSTRAINT PK_B_VI_CC DEFAULT 'Y' FOR CRM
GO

CREATE TABLE B_VOXIMPLANT_SIP
(
	ID int NOT NULL IDENTITY (1, 1),
	APP_ID varchar(128) NULL,
	CONFIG_ID int NOT NULL,
	TYPE varchar(255) NULL,
	REG_ID int NULL,
	SERVER varchar(255) NULL,
	LOGIN varchar(255) NULL,
	PASSWORD varchar(255) NULL,
	INCOMING_SERVER varchar(255) NULL,
	INCOMING_LOGIN varchar(255) NULL,
	INCOMING_PASSWORD varchar(255) NULL,
	AUTH_USER varchar(255) NULL,
	OUTBOUND_PROXY varchar(255) NULL,
	CONSTRAINT PK_B_VI_SIP PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_SIP_1 ON B_VOXIMPLANT_SIP (CONFIG_ID)
GO
ALTER TABLE B_VOXIMPLANT_SIP ADD CONSTRAINT DF_B_VIS_RI DEFAULT '0' FOR REG_ID
GO
ALTER TABLE B_VOXIMPLANT_SIP ADD CONSTRAINT DF_B_VIS_T DEFAULT 'office' FOR TYPE
GO

CREATE TABLE B_VOXIMPLANT_CONFIG
(
	ID int NOT NULL IDENTITY (1, 1),
	PORTAL_MODE varchar(50),
	SEARCH_ID varchar(255) NULL,
	PHONE_NAME varchar(255) NULL,
	PHONE_COUNTRY_CODE varchar(50) NULL,
	PHONE_VERIFIED char(1) null,
	CRM char(1) not null,
	CRM_RULE varchar(50),
	CRM_CREATE varchar(50) null,
	CRM_SOURCE varchar(50) null,
	CRM_FORWARD char(1) not null,
	CRM_TRANSFER_CHANGE char(1) null,
	QUEUE_TIME smallint,
	QUEUE_TYPE varchar(50) not null,
	DIRECT_CODE char(1) not null,
	DIRECT_CODE_RULE varchar(50),
	RECORDING char(1) not null,
	RECORDING_NOTICE char(1) null,
	RECORDING_TIME smallint,
	VOTE char(1) null,
	FORWARD_NUMBER varchar(20) NULL,
	FORWARD_LINE varchar(255) not null,
	TIMEMAN char(1) not null,
	VOICEMAIL char(1) not null,
	MELODY_LANG char(2) not null,
	MELODY_WELCOME int null,
	MELODY_WELCOME_ENABLE char(1) not null,
	MELODY_VOICEMAIL int null,
	MELODY_WAIT int null,
	MELODY_HOLD int null,
	MELODY_RECORDING int null,
	MELODY_VOTE int null,
	MELODY_VOTE_END int null,
	TO_DELETE char(1) null,
	DATE_DELETE datetime null,
	NO_ANSWER_RULE varchar(50),
	WORKTIME_ENABLE char(1) null,
	WORKTIME_FROM varchar(5) null,
	WORKTIME_TO varchar(5) null,
	WORKTIME_TIMEZONE varchar(50) null,
	WORKTIME_HOLIDAYS varchar(2000) null,
	WORKTIME_DAYOFF varchar(20) null,
	WORKTIME_DAYOFF_RULE varchar(50) null,
	WORKTIME_DAYOFF_NUMBER varchar(20) null,
	WORKTIME_DAYOFF_MELODY int null,
	CONSTRAINT PK_B_VI_CFG PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_PC_1 ON B_VOXIMPLANT_CONFIG (SEARCH_ID)
GO
CREATE INDEX IX_VI_PC_2 ON B_VOXIMPLANT_CONFIG (PORTAL_MODE)
GO
CREATE INDEX IX_VI_PC_3 ON B_VOXIMPLANT_CONFIG (TO_DELETE, DATE_DELETE)
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_FL DEFAULT 'default' FOR FORWARD_LINE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_QT DEFAULT 'evenly' FOR QUEUE_TYPE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_CC DEFAULT 'none' FOR CRM_CREATE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_PR DEFAULT 'Y' FOR PHONE_VERIFIED
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_CS DEFAULT 'CALL' FOR CRM_SOURCE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_C DEFAULT 'Y' FOR CRM
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_D DEFAULT 'Y' FOR DIRECT_CODE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_Q DEFAULT 0 FOR QUEUE_TIME
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_R DEFAULT 'Y' FOR RECORDING
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_CTC DEFAULT 'N' FOR CRM_TRANSFER_CHANGE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_RT DEFAULT 0 FOR RECORDING_TIME
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_V DEFAULT 'Y' FOR VOICEMAIL
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_VT DEFAULT 'N' FOR VOTE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_RN DEFAULT 'N' FOR RECORDING_NOTICE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_TM DEFAULT 'N' FOR TIMEMAN
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_CF DEFAULT 'Y' FOR CRM_FORWARD
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_ML DEFAULT 'EN' FOR MELODY_LANG
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_WE DEFAULT 'Y' FOR MELODY_WELCOME_ENABLE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_TD DEFAULT 'N' FOR TO_DELETE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_NAR DEFAULT 'voicemail' FOR NO_ANSWER_RULE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_WTE DEFAULT 'N' FOR WORKTIME_ENABLE
GO
ALTER TABLE B_VOXIMPLANT_CONFIG ADD CONSTRAINT PK_B_VI_IC_WTR DEFAULT 'voicemail' FOR WORKTIME_DAYOFF_RULE
GO

CREATE TABLE B_VOXIMPLANT_QUEUE
(
	ID int NOT NULL IDENTITY (1, 1),
	SEARCH_ID varchar(255) NULL,
	CONFIG_ID int NOT NULL,
	USER_ID int NOT NULL,
	STATUS varchar(50) NULL,
	LAST_ACTIVITY_DATE datetime,
	CONSTRAINT PK_B_INC_Q PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_PQ_1 ON B_VOXIMPLANT_QUEUE (CONFIG_ID)
GO
CREATE INDEX IX_VI_PQ_2 ON B_VOXIMPLANT_QUEUE (SEARCH_ID, STATUS, LAST_ACTIVITY_DATE)
GO
CREATE INDEX IX_VI_PQ_3 ON B_VOXIMPLANT_QUEUE (USER_ID)
GO

CREATE TABLE B_VOXIMPLANT_BLACKLIST
(
	ID int NOT NULL IDENTITY (1, 1),
	PHONE_NUMBER varchar(20) NULL,
	CONSTRAINT PK_B_VI_BL PRIMARY KEY (ID)
)
GO
CREATE INDEX IX_VI_BL_1 ON B_VOXIMPLANT_BLACKLIST (PHONE_NUMBER)

CREATE TABLE B_VOXIMPLANT_ROLE
(
	ID int NOT NULL IDENTITY (1, 1) PRIMARY KEY,
	NAME varchar(255) NOT NULL
)
GO

CREATE TABLE B_VOXIMPLANT_ROLE_PERMISSION
(
	ID int NOT NULL IDENTITY (1, 1) PRIMARY KEY,
	ROLE_ID int NOT NULL,
	ENTITY varchar(50) NOT NULL,
	ACTION varchar(50) NOT NULL,
	PERMISSION char(1) NULL
)
GO
CREATE INDEX IX_VOX_PERM_ROLE_ID ON B_VOXIMPLANT_ROLE_PERMISSION (ROLE_ID)
GO

CREATE TABLE B_VOXIMPLANT_ROLE_ACCESS
(
	ID int NOT NULL IDENTITY (1, 1) PRIMARY KEY,
	ROLE_ID int NOT NULL,
	ACCESS_CODE varchar(100) NOT NULL,
)
GO
CREATE INDEX IX_VOX_ACCESS_ROLE_ID ON B_VOXIMPLANT_ROLE_ACCESS (ROLE_ID)
GO