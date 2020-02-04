
drop table if exists working_rule;

create table working_rule
(
    id int auto_increment,
    user_id int not null,
    weekday enum('montag', 'dienstag', 'mittwoch', 'donnerstag', 'freitag', 'samstag', 'sonntag') not null,
    calendar_week enum('alle', 'gerade', 'ungerade') not null,
    flex_time_begin time null,
    flex_time_end time null,
    core_time_begin time null,
    core_time_end time null,
    core_time enum('ja', 'nein') default 'ja' not null,
    target time null,
    valid_from date not null,
    valid_to date null,
    constraint working_rule_pk
        primary key (id),
    constraint working_rule_user_id_fk
        foreign key (user_id) references user (id)
);

create index working_rule_user_id_index
    on working_rule (user_id);
