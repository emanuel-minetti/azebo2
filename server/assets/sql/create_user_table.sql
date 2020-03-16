drop table if exists user;

create table user
(
    id            int auto_increment
        primary key,
    username      varchar(30) not null,
    password_hash varchar(80) null,
    name          varchar(20) not null,
    given_name    varchar(20) not null,
    constraint user_username_uindex
        unique (username)
);
