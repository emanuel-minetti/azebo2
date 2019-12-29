drop table  if exists working_day;
create table working_day
(
    id int auto_increment,
    user_id int not null,
    date date not null,
    begin time null,
    end time null,
    time_off varchar(20) null,
    comment text null,
    break boolean default false not null,
    afternoon boolean default false not null,
    afternoon_begin time null,
    afternoon_end time null,
    constraint working_day_pk
        primary key (id),
    constraint working_day_user_id_fk
        foreign key (user_id) references user (id)
);

create unique index working_day_user_id_date_uindex
    on working_day (user_id, date);
