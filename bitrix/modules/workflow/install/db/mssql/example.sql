SET IDENTITY_INSERT B_WORKFLOW_STATUS ON
GO
INSERT INTO B_WORKFLOW_STATUS (ID, TIMESTAMP_X, C_SORT, ACTIVE, TITLE, DESCRIPTION, IS_FINAL, NOTIFY) VALUES (1, getdate(), 300, 'Y', 'Published', null, 'Y', 'N')
GO
INSERT INTO B_WORKFLOW_STATUS (ID, TIMESTAMP_X, C_SORT, ACTIVE, TITLE, DESCRIPTION, IS_FINAL, NOTIFY) VALUES (2, getdate(), 100, 'Y', 'Draft', null, 'N', 'N')
GO
INSERT INTO B_WORKFLOW_STATUS (ID, TIMESTAMP_X, C_SORT, ACTIVE, TITLE, DESCRIPTION, IS_FINAL, NOTIFY) VALUES (3, getdate(), 200, 'Y', 'Ready', null, 'N', 'Y')
GO
SET IDENTITY_INSERT B_WORKFLOW_STATUS OFF
GO