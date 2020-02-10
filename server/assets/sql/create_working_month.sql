drop table if exists working_month;
create table working_month
(
    id                     int auto_increment
        primary key,
    user_id                int                  not null,
    month                  date                 not null,
    saldo_hours            int                  not null,
    saldo_minutes          int                  not null,
    saldo_positive         tinyint(1)           not null,
    holidays               int                  not null,
    working_time_reduction int        default 0 not null comment 'The german "Arbeitszeitverkürzung" or "AZV".',
    archived               tinyint(1) default 0 not null,
    carried                tinyint(1) default 0 not null,
    constraint working_month_user_id_month_uindex
        unique (user_id, month),
    constraint working_month_user_id_fk
        foreign key (user_id) references user (id)
);