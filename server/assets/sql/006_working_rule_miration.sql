alter table working_rule
    drop column weekday;

alter table working_rule
    drop column calendar_week;

alter table working_rule
    drop column flex_time_begin;

alter table working_rule
    drop column flex_time_end;

alter table working_rule
    drop column core_time_begin;

alter table working_rule
    drop column core_time_end;

alter table working_rule
    drop column target;

alter table working_rule
    add has_weekdays TINYINT default 0 not null;

create table azebo2.working_time_weekday
(
    id              int auto_increment,
    working_rule_id int not null,
    weekday         int not null,
    constraint working_time_weekday_pk
        primary key (id),
    constraint working_time_weekday_pk2
        unique (working_rule_id, id),
    constraint working_time_weekday_working_rule_user_id_fk
        foreign key (working_rule_id) references azebo2.working_rule (user_id)
);
