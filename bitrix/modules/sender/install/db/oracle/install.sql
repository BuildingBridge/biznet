CREATE TABLE b_sender_list
(
  ID		NUMBER(18)		NOT NULL,
  NAME		VARCHAR(100 CHAR)	NULL,
  CODE		VARCHAR(60 CHAR)	NULL,
  SORT		NUMBER(18)		DEFAULT 100 NOT NULL,
  CONSTRAINT PK_SENDER_LIST PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_list
/
CREATE OR REPLACE TRIGGER b_sender_list_insert
BEFORE INSERT
ON b_sender_list
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_list.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE TABLE b_sender_contact
(
  ID		NUMBER(18)		NOT NULL,
  DATE_INSERT	DATE	NOT NULL,
  DATE_UPDATE	DATE	NULL,
  NAME		VARCHAR(255 CHAR)	NULL,
  EMAIL		VARCHAR(255 CHAR)	NULL,
  PHONE		VARCHAR(20 CHAR)	NULL,
  USER_ID		NUMBER(18)	NULL,
  CONSTRAINT PK_SENDER_CONTACT PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_contact
/
CREATE OR REPLACE TRIGGER b_sender_contact_insert
BEFORE INSERT
ON b_sender_contact
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_contact.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_CONTACT_EMAIL on b_sender_contact(EMAIL)
/
CREATE TABLE b_sender_contact_list
(
  CONTACT_ID	NUMBER(18) NOT NULL,
  LIST_ID	NUMBER(18) NOT NULL,
  CONSTRAINT UK_SENDER_CONTACT_LIST UNIQUE (CONTACT_ID,LIST_ID)
)
/
CREATE TABLE b_sender_group
(
  ID		NUMBER(18)		NOT NULL,
  NAME		VARCHAR(100 CHAR)	NULL,
  DESCRIPTION	CLOB		NULL,
  SORT		NUMBER(18)		DEFAULT 100 NOT NULL,
  ACTIVE		CHAR(1 CHAR)		DEFAULT 'Y' NOT NULL,
  ADDRESS_COUNT	NUMBER(18)	DEFAULT 0 NOT NULL,
  CONSTRAINT PK_SENDER_GROUP PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_group
/
CREATE OR REPLACE TRIGGER b_sender_group_insert
BEFORE INSERT
ON b_sender_group
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_group.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE TABLE b_sender_group_connector
(
  GROUP_ID NUMBER(18) NOT NULL,
  NAME		VARCHAR(100 CHAR)	NULL,
  ENDPOINT	CLOB	NULL,
  ADDRESS_COUNT	NUMBER(18)	DEFAULT 0 NOT NULL
)
/
CREATE INDEX IX_SENDER_GROUP_CONNECTOR on b_sender_group_connector(GROUP_ID)
/
CREATE TABLE b_sender_mailing
(
  ID          NUMBER(18)             NOT NULL,
  DATE_INSERT DATE            NULL,
  NAME        VARCHAR(100 CHAR)        NULL,
  DESCRIPTION	CLOB		NULL,
  ACTIVE      CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
  SITE_ID		CHAR(2 CHAR)		NOT NULL,
  SORT		NUMBER(18)		DEFAULT 100 NOT NULL,
  IS_PUBLIC CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
  TRACK_CLICK CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
  TRIGGER_FIELDS CLOB NULL,
  EMAIL_FROM VARCHAR(255 CHAR) NULL,
  IS_TRIGGER char(1 CHAR) default 'N'  NOT NULL,
  CONSTRAINT PK_SENDER_MAILING PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_mailing
/
CREATE OR REPLACE TRIGGER b_sender_mailing_insert
BEFORE INSERT
ON b_sender_mailing
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_mailing.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE TABLE b_sender_mailing_chain
(
  ID		NUMBER(18)		NOT NULL,
  MAILING_ID	NUMBER(18) NOT NULL,
  STATUS		CHAR(1 CHAR)		NOT NULL,
  POSTING_ID	NUMBER(18) NULL,
  CREATED_BY	NUMBER(18) NULL,
  PARENT_ID NUMBER(18) NULL,
  IS_TRIGGER char(1 CHAR) default 'N'  NOT NULL,
  DATE_INSERT DATE NULL,
  TIME_SHIFT NUMBER(18) default 0 NOT NULL,
  LAST_EXECUTED	DATE	NULL,
  AUTO_SEND_TIME	DATE	NULL,
  TITLE VARCHAR2(255 CHAR) NULL,
  EMAIL_FROM VARCHAR2(255 CHAR) NOT NULL,
  SUBJECT VARCHAR2(255 CHAR) NULL,
  PRIORITY VARCHAR2(60 CHAR) NULL,
  LINK_PARAMS VARCHAR2(255 CHAR) NULL,
  MESSAGE CLOB NULL,
  TEMPLATE_TYPE VARCHAR2(30 CHAR) NULL,
  TEMPLATE_ID VARCHAR2(60 CHAR) NULL,
  REITERATE	CHAR(1 CHAR)		DEFAULT 'N' NOT NULL,
  DAYS_OF_MONTH	VARCHAR(100 CHAR)	NULL,
  DAYS_OF_WEEK	VARCHAR(15 CHAR)	NULL,
  TIMES_OF_DAY	VARCHAR(255 CHAR)	NULL,
  CONSTRAINT PK_SENDER_MAILING_CHAIN PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_mailing_chain
/
CREATE OR REPLACE TRIGGER b_sender_mailing_chain_insert
BEFORE INSERT
ON b_sender_mailing_chain
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_mailing_chain.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_MAILING_CHAIN_MLNG on b_sender_mailing_chain(MAILING_ID, STATUS)
/
CREATE INDEX IX_SENDER_MAILING_CHAIN_REIT on b_sender_mailing_chain(REITERATE, STATUS)
/
CREATE TABLE b_sender_mailing_group
(
  MAILING_ID	NUMBER(18) NOT NULL,
  GROUP_ID	NUMBER(18) NOT NULL,
  INCLUDE	NUMBER(1)		DEFAULT 0 NOT NULL,
  CONSTRAINT UK_SENDER_MAILING_GROUP UNIQUE (MAILING_ID,GROUP_ID, INCLUDE)
)
/
CREATE TABLE b_sender_mailing_subscription
(
  MAILING_ID NUMBER(18) not null,
  CONTACT_ID NUMBER(18) not null,
  DATE_INSERT DATE NULL,
  IS_UNSUB CHAR(1 CHAR) DEFAULT 'N'  NOT NULL,
  PRIMARY KEY (MAILING_ID, CONTACT_ID, IS_UNSUB)
)

/
CREATE TABLE b_sender_posting
(
  ID		NUMBER(18)		NOT NULL,
  DATE_UPDATE	DATE	NULL,
  MAILING_ID	NUMBER(18) NOT NULL,
  MAILING_CHAIN_ID	NUMBER(18) NOT NULL,
  STATUS		CHAR(1 CHAR)		DEFAULT 'D' NOT NULL,
  DATE_SENT	DATE	NULL,
  DATE_CREATE	DATE	NULL,
  COUNT_SEND_ALL  NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_SEND_NONE  NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_SEND_ERROR  NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_SEND_SUCCESS  NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_SEND_DENY  NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_READ  NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_CLICK NUMBER(18) DEFAULT 0  NOT NULL,
  COUNT_UNSUB NUMBER(18) DEFAULT 0  NOT NULL,
  CONSTRAINT PK_SENDER_POSTING PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_posting
/
CREATE OR REPLACE TRIGGER b_sender_posting_insert
BEFORE INSERT
ON b_sender_posting
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_posting.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_POSTING_MLNG_CHAIN on b_sender_posting(MAILING_ID, STATUS)
/
CREATE INDEX IX_SENDER_POSTING_MAILING on b_sender_posting(MAILING_CHAIN_ID, STATUS)
/
CREATE TABLE b_sender_posting_recipient
(
  ID NUMBER(18) NOT NULL,
  POSTING_ID NUMBER(18) NOT NULL,
  STATUS CHAR(1 CHAR) NOT NULL,
  DATE_SENT	DATE	NULL,
  NAME		VARCHAR(255 CHAR)	NULL,
  EMAIL		VARCHAR(255 CHAR)	NULL,
  PHONE		VARCHAR(20 CHAR)	NULL,
  USER_ID NUMBER(18) NULL,
  DATE_DENY DATE NULL,
  FIELDS VARCHAR(2000 CHAR) NULL,
  ROOT_ID NUMBER(18) NULL,
  IS_READ CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
  IS_CLICK CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
  IS_UNSUB CHAR(1 CHAR) DEFAULT 'N' NOT NULL,
  CONSTRAINT PK_SENDER_POSTING_RECIPIENT PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_posting_recipient
/
CREATE OR REPLACE TRIGGER b_sender_posting_rcpt_insert
BEFORE INSERT
ON b_sender_posting_recipient
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_posting_recipient.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_POSTING_RECIP_EMAIL on b_sender_posting_recipient(EMAIL)
/
CREATE INDEX IX_SENDER_POSTING_RECIP_1 on b_sender_posting_recipient(POSTING_ID, STATUS)
/
CREATE TABLE b_sender_posting_read
(
  ID NUMBER(18) NOT NULL,
  POSTING_ID NUMBER(18) NOT NULL,
  RECIPIENT_ID NUMBER(18),
  DATE_INSERT	DATE	NULL,
  CONSTRAINT PK_SENDER_POSTING_READ PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_posting_read
/
CREATE OR REPLACE TRIGGER b_sender_posting_read_insert
BEFORE INSERT
ON b_sender_posting_read
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_posting_read.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_POSTING_READ on b_sender_posting_read(POSTING_ID, RECIPIENT_ID)
/
CREATE INDEX ix_b_sender_posting_read_rpid ON b_sender_posting_read (RECIPIENT_ID)
/
CREATE TABLE b_sender_posting_click
(
  ID NUMBER(18) NOT NULL,
  POSTING_ID NUMBER(18) NOT NULL,
  RECIPIENT_ID NUMBER(18),
  DATE_INSERT	DATE	NULL,
  URL	VARCHAR(2000 CHAR) 	NULL,
  CONSTRAINT PK_SENDER_POSTING_CLICK PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_posting_click
/
CREATE OR REPLACE TRIGGER b_sender_posting_click_insert
BEFORE INSERT
ON b_sender_posting_click
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_posting_click.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_POSTING_CLICK on b_sender_posting_click(POSTING_ID, RECIPIENT_ID)
/

CREATE TABLE b_sender_posting_unsub
(
  ID NUMBER(18) NOT NULL,
  RECIPIENT_ID NUMBER(18) NOT NULL,
  POSTING_ID NUMBER(18) NOT NULL,
  DATE_INSERT	DATE	NULL,
  CONSTRAINT PK_SENDER_POSTING_UNSUB PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_posting_unsub
/
CREATE OR REPLACE TRIGGER b_sender_posting_unsub_insert
BEFORE INSERT
ON b_sender_posting_unsub
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_posting_unsub.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE INDEX IX_SENDER_POSTING_UNSUB on b_sender_posting_unsub(POSTING_ID, RECIPIENT_ID)
/
CREATE TABLE b_sender_preset_template
(
  ID          NUMBER(18) NOT NULL,
  ACTIVE      CHAR(1 CHAR) DEFAULT 'Y' NOT NULL,
  NAME        VARCHAR(50 CHAR) NOT NULL,
  CONTENT     CLOB        NULL,
  CONSTRAINT PK_SENDER_PRESET_TEMPLATE PRIMARY KEY (ID)
)
/
CREATE SEQUENCE sq_b_sender_preset_template
/
CREATE OR REPLACE TRIGGER b_sender_preset_tmplt_insert
BEFORE INSERT
ON b_sender_preset_template
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
  BEGIN
    IF :NEW.ID IS NULL THEN
      SELECT sq_b_sender_preset_template.NEXTVAL INTO :NEW.ID FROM dual;
    END IF;
  END;
/
CREATE TABLE b_sender_mailing_attachment
(
  CHAIN_ID NUMBER(18) not null,
  FILE_ID NUMBER(18) not null,
  PRIMARY KEY (CHAIN_ID, FILE_ID)
)
/
CREATE TABLE b_sender_mailing_trigger
(
  MAILING_CHAIN_ID NUMBER(18) not null,
  IS_TYPE_START NUMBER(18) default 1 not null,
  NAME VARCHAR(255 CHAR) NULL,
  EVENT VARCHAR(255 CHAR) NOT NULL,
  ENDPOINT CLOB NOT NULL
)
/