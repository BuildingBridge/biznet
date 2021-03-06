CREATE TABLE B_BITRIXCLOUD_OPTION
(
	ID NUMBER(18) NOT NULL,
	NAME VARCHAR2(50 CHAR) NOT NULL,
	SORT NUMBER(18) NOT NULL,
	PARAM_KEY VARCHAR2(50 CHAR),
	PARAM_VALUE VARCHAR2(200 CHAR),
	CONSTRAINT pk_b_bitrixcloud_option PRIMARY KEY (ID)
)
/
CREATE INDEX ix_b_bitrixcloud_option_1 on B_BITRIXCLOUD_OPTION(NAME)
/
CREATE SEQUENCE SQ_B_BITRIXCLOUD_OPTION
/
CREATE OR REPLACE TRIGGER B_BITRIXCLOUD_OPTION_INSERT
BEFORE INSERT
ON B_BITRIXCLOUD_OPTION
REFERENCING OLD AS OLD NEW AS NEW
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
	 	SELECT SQ_B_BITRIXCLOUD_OPTION.NEXTVAL INTO :NEW.ID FROM DUAL;
	END IF;
END;
/
