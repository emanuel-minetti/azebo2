alter table working_month
    add finalized tinyint(1) default 0 null after saldo_positive;
