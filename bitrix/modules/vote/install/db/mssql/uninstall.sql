ALTER TABLE B_VOTE_QUESTION DROP CONSTRAINT FK_B_VOTE_QUESTION_B_VOTE
GO
ALTER TABLE B_VOTE_ANSWER DROP CONSTRAINT FK_B_VOTE_ANSWER_B_VOTE_QUESTION
GO
ALTER TABLE B_VOTE_EVENT_QUESTION DROP CONSTRAINT FK_B_VOTE_EVENT_QUESTION_B_VOTE_EVENT
GO
ALTER TABLE B_VOTE_EVENT_ANSWER DROP CONSTRAINT FK_B_VOTE_EVENT_ANSWER_B_VOTE_EVENT_QUESTION
GO
DROP TABLE B_VOTE_CHANNEL
GO
DROP TABLE B_VOTE_CHANNEL_2_GROUP
GO
DROP TABLE B_VOTE_CHANNEL_2_SITE 
GO
DROP TABLE B_VOTE 
GO
DROP TABLE B_VOTE_QUESTION
GO
DROP TABLE B_VOTE_ANSWER 
GO
DROP TABLE B_VOTE_EVENT
GO
DROP TABLE B_VOTE_EVENT_QUESTION
GO
DROP TABLE B_VOTE_EVENT_ANSWER
GO
DROP TABLE B_VOTE_USER
GO
DROP TABLE B_VOTE_ATTACHED_OBJECT
GO