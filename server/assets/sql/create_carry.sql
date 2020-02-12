drop table if exists carry;

create table carry
(
    id                     int auto_increment
        primary key,
    user_id                int        not null,
    year                   date       not null,
    saldo_hours            int        not null,
    saldo_minutes          int        not null,
    saldo_positive         tinyint(1) not null,
    holidays               int        not null,
    holidays_previous_year int        not null,
    constraint carry_user_id_month_uindex
        unique (user_id, year),
    constraint carry_user_id_fk
        foreign key (user_id) references user (id)
);