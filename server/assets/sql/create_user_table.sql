create table user
(
    id int auto_increment,
    username varchar(30) not null,
    password_hash varchar(80) not null,
    name varchar(20) null,
    given_name varchar(20) not null,
    constraint user_pk
        primary key (id)
);

create unique index user_username_uindex
    on user (username);
