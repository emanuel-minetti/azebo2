drop table  if exists working_day;
create table working_day
(
    id              int auto_increment
        primary key,
    user_id         int                  not null,
    date            date                 not null,
    begin           time                 null,
    end             time                 null,
    time_off        varchar(20)          null,
    comment         text                 null,
    break           tinyint(1) default 0 not null,
    afternoon       tinyint(1) default 0 not null,
    afternoon_begin time                 null,
    afternoon_end   time                 null,
    constraint working_day_user_id_date_uindex
        unique (user_id, date),
    constraint working_day_user_id_fk
        foreign key (user_id) references user (id)
);
