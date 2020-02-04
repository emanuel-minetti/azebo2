
drop table if exists working_rule;

create table working_rule
(
    id              int auto_increment
        primary key,
    user_id         int                                                                                    not null,
    weekday         tinyint(1)                                                                             not null,
    calendar_week   enum ('all', 'even', 'odd')                                                            not null,
    flex_time_begin time                                                                                   null,
    flex_time_end   time                                                                                   null,
    core_time       tinyint(1) default 1                                                                   not null,
    core_time_begin time                                                                                   null,
    core_time_end   time                                                                                   null,
    target          time                                                                                   not null,
    valid_from      date                                                                                   not null,
    valid_to        date                                                                                   null,
    constraint working_rule_user_id_fk
        foreign key (user_id) references user (id)
);

create index working_rule_user_id_index
    on working_rule (user_id);

