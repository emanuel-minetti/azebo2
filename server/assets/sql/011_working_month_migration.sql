alter table working_month
    add finalized tinyint(1) default 0 null after saldo_positive;

alter table working_month
    add saldo_capped tinyint(1) default 0 not null after saldo_positive;