DELETE FROM B_USER_OPTION WHERE NAME LIKE '~gadgets_sonet_group%'
/
DELETE FROM B_USER_OPTION WHERE NAME LIKE '~gadgets_sonet_user%'
/
DROP TABLE B_SONET_GROUP_SUBJECT CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_GROUP_SUBJECT_SITE CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_GROUP CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_GROUP_SITE CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_USER2GROUP CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_FEATURES CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_FEATURES2PERMS CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_USER_RELATIONS CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_MESSAGES CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_SMILE CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_SMILE_LANG CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_USER_PERMS CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_USER_EVENTS CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_SITE CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_COMMENT CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_EVENTS CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_EVENT_USER_VIEW CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_RIGHT CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_COUNTER CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_PAGE CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_FOLLOW CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_SMARTFILTER CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_FAVORITES CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_LOG_VIEW CASCADE CONSTRAINTS
/
DROP TABLE B_SONET_SUBSCRIPTION CASCADE CONSTRAINTS
/

DROP SEQUENCE SQ_B_SONET_GROUP_SUBJECT
/
DROP SEQUENCE SQ_B_SONET_GROUP
/
DROP SEQUENCE SQ_B_SONET_USER2GROUP
/
DROP SEQUENCE SQ_B_SONET_FEATURES
/
DROP SEQUENCE SQ_B_SONET_FEATURES2PERMS
/
DROP SEQUENCE SQ_B_SONET_USER_RELATIONS
/
DROP SEQUENCE SQ_B_SONET_MESSAGES
/
DROP SEQUENCE SQ_B_SONET_SMILE
/
DROP SEQUENCE SQ_B_SONET_SMILE_LANG
/
DROP SEQUENCE SQ_B_SONET_USER_PERMS
/
DROP SEQUENCE SQ_B_SONET_USER_EVENTS
/
DROP SEQUENCE SQ_B_SONET_LOG
/
DROP SEQUENCE SQ_B_SONET_LOG_COMMENT
/
DROP SEQUENCE SQ_B_SONET_LOG_EVENTS
/
DROP SEQUENCE SQ_B_SONET_LOG_RIGHT
/
DROP SEQUENCE SQ_B_SONET_SUBSCRIPTION
/