insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (1, 'Aport', '', '0.0000', '0.0000', null, 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (2, 'Altavista', '', '0.0000', '0.0000', null, 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (3, 'AOL', '', '0.0000', '0.0000', null, 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (5, 'Google', null, '0.0000', '0.0000', '', 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (6, 'Rambler', '', '0.0000', '0.0000', null, 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (7, 'Yandex', '', '0.0000', '0.0000', null, 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (8, 'Yahoo', null, '0.0000', '0.0000', '', 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
insert  into b_stat_adv ( ID,REFERER1,REFERER2,COST,REVENUE,EVENTS_VIEW,GUESTS,NEW_GUESTS,FAVORITES,C_HOSTS,SESSIONS,HITS,DATE_FIRST,DATE_LAST,GUESTS_BACK,FAVORITES_BACK,HOSTS_BACK,SESSIONS_BACK,HITS_BACK,DESCRIPTION,PRIORITY) values (10, 'MSN', null, '0.0000', '0.0000', '', 0, 0, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, null, 100)
/
DROP SEQUENCE SQ_B_STAT_ADV
/
CREATE SEQUENCE SQ_B_STAT_ADV START WITH 11 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/

insert into b_stat_adv_searcher ( ADV_ID,SEARCHER_ID)
select adv.ID, searcher.ID
from
	b_stat_adv adv
	inner join b_stat_searcher searcher on adv.REFERER1 = searcher.NAME
/

update b_stat_adv set REFERER1 = lower(REFERER1)
/