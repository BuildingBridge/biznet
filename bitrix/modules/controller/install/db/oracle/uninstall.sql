BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_AUTH_GRANT CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_AUTH_GRANT';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_AUTH_LOG CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_AUTH_LOG';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_GROUP_MAP CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_GROUP_MAP';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_COUNTER_VALUE CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_COUNTER_GROUP CASCADE CONSTRAINTS';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_COUNTER CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_COUNTER';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_LOG CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_LOG';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_TASK CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_TASK';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_COMMAND CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_COMMAND';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_MEMBER_LOG CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_MEMBER_LOG';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_MEMBER CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_MEMBER';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
BEGIN
	EXECUTE IMMEDIATE 'DROP TABLE B_CONTROLLER_GROUP CASCADE CONSTRAINTS';
	EXECUTE IMMEDIATE 'DROP SEQUENCE SQ_B_CONTROLLER_GROUP';
EXCEPTION
	WHEN OTHERS THEN NULL;
END;
/
