SET IDENTITY_INSERT B_FORUM_DICTIONARY ON
GO
INSERT  INTO B_FORUM_DICTIONARY (ID, TITLE,[TYPE]) VALUES (3, '[en]Bad words', 'W')
GO
INSERT  INTO B_FORUM_DICTIONARY (ID, TITLE,[TYPE]) VALUES (4, '[en]Translit', 'T')
GO
SET IDENTITY_INSERT B_FORUM_DICTIONARY OFF
GO

INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'angry', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'ass', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'asshole', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'banger', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bastard', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'batter', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bicho', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bisexual', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bitch', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'blumpkin', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'booger', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bugger*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bukakke', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bull', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bulldyke', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bullshit', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bunny', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'bunnyfuck', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'chocha', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'chode', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'clap', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'coconuts', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cohones', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cojones', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'coon', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cootch', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cooter', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cornhole', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cracka', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'crap', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cum', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cunnilingus', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'cunt*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'damn*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dark*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dick', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dickhead', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'diddle', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dildo', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dilhole', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dingleberry', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'doodle', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dork', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'dumpster', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'faggot', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'fart', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'frig', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'fuck*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'fucker', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'giz', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'goatse', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'gook', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'gringo', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'hobo', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'honky', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'jackass', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'jackoff', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'jerkoff', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'jiggaboo', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'jizz', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'kike', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'mayo', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'moose', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'nigg*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'paki', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'pecker', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'piss', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'poonanni', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'poontang', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'prick', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'punch', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'queef', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'rogue', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'sanchez', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'schlong', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'shit', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'skank', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'spaz', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'spic', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'teabag*', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'tits', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'twat', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'twot', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'vart', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'wanker', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'waste', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'wetback', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'whore', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'wigger', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'wog', '', '', '', 'Y', 'TRNSL')
GO
INSERT INTO B_FORUM_FILTER (DICTIONARY_ID,WORDS,PATTERN,REPLACEMENT,DESCRIPTION,USE_IT,PATTERN_CREATE) VALUES ('3', 'wop', '', '', '', 'Y', 'TRNSL')
GO