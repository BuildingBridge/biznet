CREATE TABLE B_PULL_STACK
(
	ID NUMBER(18) NOT NULL,
	CHANNEL_ID VARCHAR2(50 CHAR) NOT NULL,
	MESSAGE clob NOT NULL,
	DATE_CREATE date NOT NULL,
	PRIMARY KEY (ID)
)
/
CREATE INDEX IX_PULL_STACK_CID ON B_PULL_STACK (CHANNEL_ID)
/
CREATE INDEX IX_PULL_STACK_D ON B_PULL_STACK (DATE_CREATE)
/

CREATE SEQUENCE SQ_B_PULL_STACK
/
CREATE OR REPLACE TRIGGER B_PULL_STACK_insert
BEFORE INSERT
ON B_PULL_STACK
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_PULL_STACK.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_PULL_CHANNEL
(
	ID NUMBER(18) NOT NULL,
	USER_ID NUMBER(18) NOT NULL,
	CHANNEL_TYPE VARCHAR2(50 CHAR) NULL,
	CHANNEL_ID VARCHAR2(50 CHAR) NOT NULL,
	LAST_ID NUMBER(18) NOT NULL,
	DATE_CREATE date NOT NULL,
	PRIMARY KEY (ID)
)
/
CREATE INDEX IX_PULL_CN_CID ON B_PULL_CHANNEL (CHANNEL_ID)
/
CREATE INDEX IX_PULL_CN_D ON B_PULL_CHANNEL (DATE_CREATE)
/
CREATE UNIQUE INDEX IX_PULL_CN_UID ON B_PULL_CHANNEL(USER_ID, CHANNEL_TYPE)
/
CREATE SEQUENCE SQ_B_PULL_CHANNEL
/
CREATE OR REPLACE TRIGGER B_PULL_CHANNEL_insert
BEFORE INSERT
ON B_PULL_CHANNEL
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_PULL_CHANNEL.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_PULL_PUSH
(
	ID NUMBER(18) NOT NULL,
	USER_ID NUMBER(18) NOT NULL,
	APP_ID VARCHAR2(50 CHAR) NULL,
	UNIQUE_HASH VARCHAR2(50 CHAR) NULL,
	DEVICE_TYPE VARCHAR2(50 CHAR) NULL,
	DEVICE_ID VARCHAR2(255 CHAR) NULL,
	DEVICE_NAME VARCHAR2(50 CHAR) NULL,
	DEVICE_TOKEN VARCHAR2(255 CHAR) NOT NULL,
	DATE_CREATE date NOT NULL,
	DATE_AUTH date NULL,
	PRIMARY KEY (ID)
)
/
CREATE INDEX IX_PULL_PSH_UID ON B_PULL_PUSH (USER_ID)
/
CREATE INDEX IX_PULL_PSH_UH ON B_PULL_PUSH (UNIQUE_HASH)
/
CREATE SEQUENCE SQ_B_PULL_PUSH
/
CREATE OR REPLACE TRIGGER B_PULL_PUSH_insert
BEFORE INSERT
ON B_PULL_PUSH
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_PULL_PUSH.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_PULL_PUSH_QUEUE
(
	ID NUMBER(18) NOT NULL,
	USER_ID NUMBER(18) NOT NULL,
	APP_ID VARCHAR2(50 CHAR) NULL,
	TAG VARCHAR2(255 CHAR) NULL,
	SUB_TAG VARCHAR2(255 CHAR) NULL,
	MESSAGE clob NULL,
	PARAMS clob NULL,
	ADVANCED_PARAMS clob NULL,
	BADGE NUMBER(11) NULL,
	DATE_CREATE date NULL,
	PRIMARY KEY (ID)
)
/
CREATE INDEX IX_PULL_PSHQ_UID ON B_PULL_PUSH_QUEUE (USER_ID)
/
CREATE INDEX IX_PULL_PSHQ_AID ON B_PULL_PUSH_QUEUE (APP_ID)
/
CREATE INDEX IX_PULL_PSHQ_UT ON B_PULL_PUSH_QUEUE (USER_ID, TAG)
/
CREATE INDEX IX_PULL_PSHQ_UST ON B_PULL_PUSH_QUEUE (USER_ID, SUB_TAG)
/
CREATE INDEX IX_PULL_PSHQ_DC ON B_PULL_PUSH_QUEUE (DATE_CREATE)
/
CREATE SEQUENCE SQ_B_PULL_PUSH_QUEUE
/
CREATE OR REPLACE TRIGGER B_PULL_PUSH_QUEUE_insert
BEFORE INSERT
ON B_PULL_PUSH_QUEUE
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_PULL_PUSH_QUEUE.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/

CREATE TABLE B_PULL_WATCH
(
	ID NUMBER(18) NOT NULL,
	USER_ID NUMBER(18) NOT NULL,
	CHANNEL_ID VARCHAR2(50 CHAR) NOT NULL,
	TAG VARCHAR2(255 CHAR) NOT NULL,
	DATE_CREATE date NOT NULL,
	PRIMARY KEY (ID)
)
/
CREATE INDEX IX_PULL_W_UT ON B_PULL_WATCH (USER_ID, TAG)
/
CREATE INDEX IX_PULL_W_D ON B_PULL_WATCH (DATE_CREATE)
/
CREATE INDEX IX_PULL_W_T ON B_PULL_WATCH (TAG)
/
CREATE SEQUENCE SQ_B_PULL_WATCH
/
CREATE OR REPLACE TRIGGER B_PULL_WATCH_insert
BEFORE INSERT
ON B_PULL_WATCH
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
 		SELECT SQ_B_PULL_WATCH.NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;
/


